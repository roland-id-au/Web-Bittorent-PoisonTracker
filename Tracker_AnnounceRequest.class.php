<?php
class Tracker_AnnounceRequest extends Tracker_Request {
	private $Tracker;
	
	public function __construct(Tracker $Tracker){
		$this->Tracker = $Tracker;
		$this->RequireParameters('info_hash', 'peer_id', 'port', 'uploaded', 'downloaded', 'left');
	}

	protected function MapParameter($Parameter){
		switch($Parameter){
			case 'ip':
				return $this->GetParameter('ip', $_SERVER['REMOTE_ADDR']);
				break;
			case 'event':
				return $this->GetParameter('event', 'none');
				break;
			case 'compact':
				return $this->GetParameter('compact', false);
				break;
			case 'num_want':
				$NumWant = $this->GetParameter('num_want', 30);
				if($NumWant > 30){return 30;}
				break;
			case 'passkey':
				return $this->GetParameter('passkey', '');
				break;
			default:
				return parent::MapParameter($Parameter);
		}
	}

	public function GetResponse(Tracker_Torrent $Torrent){
		$ForgeryProvider = new Tracker_ForgeryProvider($this->Tracker->Configuration->Forgery->MaximumFrequencyMultiplier, $this->Tracker->Configuration->Forgery->MinimumFrequencyMultiplier);
		
		$Seeds = Tracker_Data::GetTorrentSeeds($Torrent);
		$Leeches = Tracker_Data::GetTorrentLeechers($Torrent);
		shuffle($Seeds);
		shuffle($Leeches);
		
		$SeedCount = $ForgeryProvider->GetNextPeerCount($this->Tracker->Configuration->Forgery->BaseSeedCount, $this->Tracker->Configuration->Forgery->MaximumSeedAmplitude, $this->Tracker->Configuration->Forgery->MinimumSeedAmplitude, $Torrent->Double1, $Torrent->Double2, $Torrent->Double3, $Torrent->Double4, $Torrent->Long1);
		$LeechCount = $ForgeryProvider->GetNextPeerCount($this->Tracker->Configuration->Forgery->BaseLeechCount, $this->Tracker->Configuration->Forgery->MaximumLeechAmplitude, $this->Tracker->Configuration->Forgery->MinimumLeechAmplitude, $Torrent->Double3, $Torrent->Double4, $Torrent->Double1, $Torrent->Double2, $Torrent->Long1);	
		
		$FakeSeedsRequired = count($Seeds) - $SeedCount;
		if($FakeSeedsRequired < 0){$FakeSeedsRequired = abs($FakeSeedsRequired);}
		$FakeLeechesRequired = count($Leeches) - $LeechCount;
		if($FakeLeechesRequired < 0){$FakeLeechesRequired = abs($FakeLeechesRequired);}
		
		$NumWant = ($FakeSeedsRequired + $FakeLeechesRequired) > $this->num_want ? $this->num_want : ($FakeSeedsRequired + $FakeLeechesRequired);

		$Response = new BEncodedDictionary();
		
		$Response['interval'] = $this->Tracker->Configuration->Tracker->AnnounceInterval;
		$Response['complete'] = $SeedCount;
		$Response['incomplete'] = $LeechCount;
		
		if($this->compact){
			$Response['peers'] = "";
			$Buffer = array();
			
			foreach($Seeds as $Seed){
				$Buffer[] = pack("N", ip2long($Seed->Ip));
				$Buffer[] = pack("n", $Seed->Port);
				$NumWant--;
			}

			foreach($Leeches as $Leech){
				$Buffer[] = pack("N", ip2long($Leech->Ip));
				$Buffer[] = pack("n", $Leech->Port);
				$NumWant--;
			}
			for($i = 0; $i < $NumWant; $i++){
				$PeerId = null;
				$PeerIp = null;
				$PeerPort = null;
				Tracker_ForgeryProvider::GetRandomPeer($this->Tracker->Configuration->Forgery->GetDelimited('ForgedHosts'), $PeerId, $PeerIp, $PeerPort);
				$Buffer[] = pack("N", ip2long($PeerIp));
				$Buffer[] = pack("n", $PeerPort);
			}
			$Response['peers'] = implode('', $Buffer);
		}else{
			$Response['peers'] = new BEncodedList();
			foreach($Seeds as $Seed){
				$PeerDictionary = new BEncodedDictionary();
				$PeerDictionary['peer id'] = $Seed->RawId;
				$PeerDictionary['ip'] = $Seed->Ip;
				$PeerDictionary['port'] = $Seed->Port;
				$Response['peers'][] = $PeerDictionary;
				$NumWant--;
			}

			foreach($Leeches as $Leech){
				$PeerDictionary = new BEncodedDictionary();
				$PeerDictionary['peer id'] = $Leech->RawId;
				$PeerDictionary['ip'] = $Leech->Ip;
				$PeerDictionary['port'] = $Leech->Port;
				$Response['peers'][] = $PeerDictionary;
				$NumWant--;
			}
			
			for($i = 0; $i < $NumWant; $i++){
				$PeerId = null;
				$PeerIp = null;
				$PeerPort = null;
				Tracker_ForgeryProvider::GetRandomPeer($this->Tracker->Configuration->Forgery->GetDelimited('ForgedHosts'), $PeerId, $PeerIp, $PeerPort);		
				$PeerDictionary = new BEncodedDictionary();
				$PeerDictionary['peer id'] = $PeerId;
				$PeerDictionary['ip'] = $PeerIp;
				$PeerDictionary['port'] = $PeerPort;
				$Response['peers'][] = $PeerDictionary;
			}
		}
		return $Response->Encode();
	}

	public function GetIdentifier(){
		$Identifier = __CLASS__.$this->num_want.$this->passkey.$this->info_hash;
		return md5($Identifier);
	}
}
?>
