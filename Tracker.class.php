<?php
class Tracker {
	/**
	 * Tracker Configuration object
	 *
	 * @var Tracker_Configuration
	 */
	public $Configuration;
	/**
	 * Tracker Cache object
	 *
	 * @var Tracker_Cache
	 */
	private $Cache;

	/**
	 * Ctor
	 *
	 * @param string $IniPath
	 */
	public function __construct($IniPath){
		// Load Tracker_Configuration
		$this->Configuration = new Tracker_Configuration($IniPath);
		
		// Set up Cache and Database connections
		if($this->Configuration->Database->Persistant){
			if(!mysql_pconnect($this->Configuration->Database->Hostname, $this->Configuration->Database->Username, $this->Configuration->Database->Password)){
				throw new Tracker_Exception("Database connection failed");
			}
			if(!mysql_select_db($this->Configuration->Database->Database)){
				throw new Tracker_Exception("Database selection failed");
			}
		}else{
			if(!mysql_connect($this->Configuration->Database->Hostname, $this->Configuration->Database->Username, $this->Configuration->Database->Password)){
				throw new Tracker_Exception("Database connection failed");
			}
			if(!mysql_select_db($this->Configuration->Database->Database)){
				throw new Tracker_Exception("Database selection failed");
			}
		}
		if($this->Configuration->Memcache->Enabled){
			$this->Cache = new Tracker_Cache($this->Configuration->Memcache->Hostname, $this->Configuration->Memcache->Port, $this->Configuration->Memcache->Persistant);
		}else{
			$this->Cache = null;
		}
	}

	/**
	 * Tracker Announce request handler
	 *
	 */
	public function Announce(){
		if(!$this->Configuration->Tracker->Online){
			throw new Exception('Tracker offline!');
		}
		// Tracker_AnnounceRequest object, self populating
		$AnnounceRequest = new Tracker_AnnounceRequest($this);
		
		$SafeInfoHash = $AnnounceRequest->GetHash('info_hash');
		$SafePeerId = $AnnounceRequest->GetHash('peer_id');
		
		if($AnnounceRequest->HasParameter('passkey')){
			$Override = null;
			if(Tracker_ForgeryProvider::GetConfigurationOverride($this->Configuration->Tracker->ConfigurationOverride, $AnnounceRequest->passkey, $Override)){
				$this->Configuration->Forgery->Load($Override);
			}else{
				// Override does not exist
			}
		}
		
		// Check if the torrent announced actually exists
		if(Tracker_Data::GetTorrentExists($SafeInfoHash)){
			$Torrent = Tracker_Data::GetTorrent($SafeInfoHash);
		}else{
			// If not, create a new one
			$Torrent = new Tracker_Torrent($SafeInfoHash, $AnnounceRequest->info_hash, null, time(), 0, 0, 0, 0, 0, 0);
		}
		
		// Allow torrents not in the network specified by tracker configuration "TrustedHosts"?
		if(!$this->Configuration->Tracker->AllowAnonymous){
			// Authorisation is required to announce for this torrent
			if(!$Torrent->GetAuthorisation()){
				// Torrent is not current authorised
				if(in_array($AnnounceRequest->ip, $this->Configuration->Forgery->GetDelimited('ForgedHosts'))){
					// Torrent is present in network, set authorisation
					Tracker_Data::SetTorrentAuthorised($SafeInfoHash);
				}else{
					// Torrent is not present in network, deny request
					throw new Tracker_Exception("Too many birds in the nest");
				}
			}
		}

		// Check if we are already tracking this peer and populate Tracker_Peer object if so
		if(Tracker_Data::GetPeerExists($SafePeerId, $SafeInfoHash)){
			$Peer = Tracker_Data::GetPeer($SafePeerId, $SafeInfoHash);
			$Peer->Ip = $AnnounceRequest->ip;
			$Peer->Port = $AnnounceRequest->port;
			$Peer->Uploaded = $AnnounceRequest->uploaded;
			$Peer->Downloaded = $AnnounceRequest->downloaded;
			$Peer->Left = $AnnounceRequest->left;
		}else{
			// Peer does not exist, create new Tracker_Peer object
			$Peer = new Tracker_Peer($SafePeerId, $AnnounceRequest->peer_id, $SafeInfoHash, $AnnounceRequest->ip, $AnnounceRequest->port, $AnnounceRequest->uploaded, $AnnounceRequest->downloaded, $AnnounceRequest->left);
		}

		// There may have been an event passed with this request
		switch($AnnounceRequest->event){
			case 'stopped':
				// Client has gracefully stopped their download, remove them from the peer list
				Tracker_Data::RemovePeer($Peer);
				break;
			case 'completed':
				// Client has completed their download, incriment Tracker_Torrent download count
				if($Peer->Left == 0){
					$Torrent->Downloaded++;
					$Torrent->Save();
				}
				$Peer->Save();
				break;
			default:
				$Peer->Save();
		}
		// It is necessary to save Tracker_Peer data above to log stats etc.

		if($this->Configuration->Memcache->Enabled){
			$CachedRequest = null;
		
			// Attempt to fetch cached copy of response
			if($this->Cache->GetCache($AnnounceRequest->GetIdentifier(), $CachedRequest)){
				$Torrent->Save();
				echo $CachedRequest;
			}else{
				// Cache request
				$Response = $AnnounceRequest->GetResponse($Torrent);
				$Torrent->Save();
				$this->Cache->SetCache($AnnounceRequest->GetIdentifier(), $Response, $this->Configuration->Tracker->CacheLifeTime);
				echo $Response;
			}
		}else{
			$Torrent->Save();
			echo $AnnounceRequest->GetResponse($Torrent);
		}
		//$this->Update();
	}

	public function Scrape(){
		if(!$this->Configuration->Tracker->Online){
			throw new Exception('Tracker offline!');
		}
		$ScrapeRequest = new Tracker_ScrapeRequest($this);
		
		$SafeInfoHash = is_scalar($ScrapeRequest->info_hash) ? $ScrapeRequest->GetHash('info_hash') : md5(implode(null, $ScrapeRequest->info_hash));
		
		if($ScrapeRequest->HasParameter('passkey')){
			$Override = null;
			if(Tracker_ForgeryProvider::GetConfigurationOverride($this->Configuration->Tracker->ConfigurationOverride, $ScrapeRequest->passkey, $Override)){
				$this->Configuration->Forgery->Load($Override);
			}else{
				// Override does not exist
			}
		}
		
		if(!$this->Configuration->Tracker->AllowAnonymous){
			if(is_scalar($ScrapeRequest->info_hash)){
				if(!Tracker_Data::GetTorrentAuthorised($SafeInfoHash)){
					throw new Tracker_Exception("Too many birds in the nest");
				}
			}
		}
		
		$Response = $ScrapeRequest->GetResponse();

		if(is_scalar($ScrapeRequest->info_hash)){
			if(!Tracker_Data::GetTorrentExists($SafeInfoHash)){
				$Torrent = new Tracker_Torrent($SafeInfoHash, $ScrapeRequest->info_hash, null, time(), 0, 0, 0, 0, 0, 0);
				$Torrent->Save();
			}
		}
		else{
			foreach($ScrapeRequest->info_hash as $Hash){
				$SafeHash = md5($Hash);
				if(!Tracker_Data::GetTorrentExists($SafeHash)){
					$Torrent = new Tracker_Torrent($SafeHash, $Hash, null, time(), 0, 0, 0, 0, 0, 0);
					$Torrent->Save();
				}
			}
		}
		
		if($this->Configuration->Memcache->Enabled){
			$CachedRequest = null;

			if($this->Cache->GetCache($ScrapeRequest->GetIdentifier(), $CachedRequest)){
				echo $CachedRequest;
			}else{
				$Response = $ScrapeRequest->GetResponse();
				$this->Cache->SetCache($ScrapeRequest->GetIdentifier(), $Response, $this->Configuration->Tracker->CacheLifeTime);
				echo $Response;
			}
		}else{
			echo $ScrapeRequest->GetResponse();
		}
		//$this->Update();
	}
	
	public function Authorise(){
		if(array_key_exists('Torrent', $_FILES)){
			$Torrent = new BEncodedDictionary();
			$Torrent->FromString(file_get_contents($_FILES['Torrent']['tmp_name']));
			Tracker_Data::SetTorrentAuthorised(sha1($Torrent['info']->Encode(), true));
		}else{
			throw new Tracker_Exception("Missing torrent file");
		}
	}
	
	public function Rescan(){
		$TorrentFiles = glob(rtrim($this->Configuration->Tracker->TorrentPath, '\\/').'/*.torrent');
		foreach($TorrentFiles as $TorrentFile){
			$Torrent = new BEncodedDictionary();
			$Torrent->FromString(file_get_contents($TorrentFile));
			Tracker_Data::SetTorrentAuthorised(sha1($Torrent['info']->Encode(), true));
		}
	}

	public function Update(){
		Tracker_Data::Update($this->Configuration->Tracker->TorrentLifetime, $this->Configuration->Tracker->PeerLifetime);
	}

	public function Statistics(){

	}
}
?>
