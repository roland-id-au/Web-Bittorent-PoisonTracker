<?php
class Tracker_Data {
	
	public static function GetTorrent($TorrentHash){
		$Result = mysql_query("SELECT * FROM `Torrents` WHERE `Torrents_Hash` = '".mysql_real_escape_string($TorrentHash)."'");
		if(!$Result){throw new Tracker_Exception(mysql_error());}
		if(mysql_num_rows($Result) == 1){
			$Row = mysql_fetch_object($Result);
			return new Tracker_Torrent($Row->Torrents_Hash, $Row->Torrents_RawHash, $Row->Torrents_Added, $Row->Torrents_Updated, $Row->Torrents_Downloaded, $Row->Torrents_Double1, $Row->Torrents_Double2, $Row->Torrents_Double3, $Row->Torrents_Double4, $Row->Torrents_Long1);
		}else{
			throw new Tracker_Exception("Torrent does not exist");
		}
	}
	
	public static function GetTorrentExists($TorrentHash){
		$Result = mysql_query("SELECT * FROM `Torrents` WHERE `Torrents_Hash` = '".mysql_real_escape_string($TorrentHash)."'");
		if(!$Result){throw new Tracker_Exception(mysql_error());}
		if(mysql_num_rows($Result) == 1){
			return true;
		}else{
			return false;
		}
	}
	
	public static function GetTorrents(){
		$Torrents = array();
		$Result = $Result = mysql_query("SELECT * FROM `Torrents`");
		if(!$Result){throw new Tracker_Exception(mysql_error());}
		if(mysql_num_rows($Result) > 0){
			while($Row = mysql_fetch_object($Result)){
				$Torrents[] = new Tracker_Torrent($Row->Torrents_Hash, $Row->Torrents_RawHash, $Row->Torrents_Added, $Row->Torrents_Updated, $Row->Torrents_Downloaded, $Row->Torrents_Double1, $Row->Torrents_Double2, $Row->Torrents_Double3, $Row->Torrents_Double4, $Row->Torrents_Long1);
			}
		}
		return $Torrents;
	}
	
	public static function GetTorrentPeers(Tracker_Torrent $Torrent){
		$Peers = array();
		$Result = mysql_query("SELECT * FROM `Peers` WHERE `Torrents_Hash` = '".mysql_real_escape_string($Torrent->Hash)."'");
		if(!$Result){throw new Tracker_Exception(mysql_error());}
		if(mysql_num_rows($Result) > 0){
			while($Row = mysql_fetch_object($Result)){
				$Peers[] = new Tracker_Peer($Row->Peers_Id, $Row->Peers_RawId, $Row->Torrents_Hash, long2ip($Row->Peers_Ip), $Row->Peers_Port, $Row->Peers_Uploaded, $Row->Peers_Downloaded, $Row->Peers_Left);
			}
		}
		return $Peers;
	}
	
	public static function GetTorrentSeeds(Tracker_Torrent $Torrent){
		$Seeds = array();
		$Result = mysql_query("SELECT * FROM `Peers` WHERE `Torrents_Hash` = '".mysql_real_escape_string($Torrent->Hash)."' AND `Peers_Left` = 0");
		if(!$Result){throw new Tracker_Exception(mysql_error());}
		if(mysql_num_rows($Result) > 0){
			while($Row = mysql_fetch_object($Result)){
				$Seeds[] = new Tracker_Peer($Row->Peers_Id, $Row->Peers_RawId, $Row->Torrents_Hash, long2ip($Row->Peers_Ip), $Row->Peers_Port, $Row->Peers_Uploaded, $Row->Peers_Downloaded, $Row->Peers_Left);
			}
		}
		return $Seeds;
	}
	
	public static function GetTorrentLeechers(Tracker_Torrent $Torrent){
		$Leechers = array();
		$Result = mysql_query("SELECT * FROM `Peers` WHERE `Torrents_Hash` = '".mysql_real_escape_string($Torrent->Hash)."' AND `Peers_Left` > 0");
		if(!$Result){throw new Tracker_Exception(mysql_error());}
		if(mysql_num_rows($Result) > 0){
			while($Row = mysql_fetch_object($Result)){
				$Leechers[] = new Tracker_Peer($Row->Peers_Id, $Row->Peers_RawId, $Row->Torrents_Hash, long2ip($Row->Peers_Ip), $Row->Peers_Port, $Row->Peers_Uploaded, $Row->Peers_Downloaded, $Row->Peers_Left);
			}
		}
		return 	$Leechers;
	}
	
	public static function GetTorrentCount(){
		$Result = mysql_query("SELECT Count(*) as `TorrentCount` FROM `Torrents`");
		if(!$Result){throw new Tracker_Exception(mysql_error());}
		if(mysql_num_rows($Result) > 0){
			$Row = mysql_fetch_object($Result);
			return $Row->TorrentCount;
		}else{
			return 0;
		}
	}
	
	public static function GetTorrentPeerCount(Tracker_Torrent $Torrent){
		$Result = mysql_query("SELECT Count(*) as `PeerCount` FROM `Peers` WHERE `Torrents_Hash` = '".mysql_real_escape_string($Torrent->Hash)."'");
		if(!$Result){throw new Tracker_Exception(mysql_error());}
		if(mysql_num_rows($Result) > 0){
			$Row = mysql_fetch_object($Result);
			return $Row->PeerCount;
		}else{
			return 0;
		}
	}
	
	public static function GetTorrentSeedCount(Tracker_Torrent $Torrent){
		$Result = mysql_query("SELECT Count(*) as `PeerCount` FROM `Peers` WHERE `Torrents_Hash` = '".mysql_real_escape_string($Torrent->Hash)."' AND `Peers_Left` = 0");
		if(!$Result){throw new Tracker_Exception(mysql_error());}
		if(mysql_num_rows($Result) > 0){
			$Row = mysql_fetch_object($Result);
			return $Row->PeerCount;
		}else{
			return 0;
		}
	}
	
	public static function GetTorrentLeechCount(Tracker_Torrent $Torrent){
		$Result = mysql_query("SELECT Count(*) as `PeerCount` FROM `Peers` WHERE `Torrents_Hash` = '".mysql_real_escape_string($Torrent->Hash)."' AND `Peers_Left` > 0");
		if(!$Result){throw new Tracker_Exception(mysql_error());}
		if(mysql_num_rows($Result) > 0){
			$Row = mysql_fetch_object($Result);
			return $Row->PeerCount;
		}else{
			return 0;
		}
	}
	
	public static function GetTorrentDownloadedCount(Tracker_Torrent $Torrent){
		$Result = mysql_query("SELECT * FROM `Torrents` WHERE `Torrents_Hash` = '".mysql_real_escape_string($Torrent->Hash)."'");
		if(!$Result){throw new Tracker_Exception(mysql_error());}
		if(mysql_num_rows($Result) == 1){
			$Row = mysql_fetch_object($Result);
			return $Row->Downloads;
		}else{
			return 0;
		}
	}
	
	public static function GetTorrentAuthorised($TorrentHash){
		$Result = mysql_query("SELECT * FROM `Authorisation` WHERE `Torrents_Hash` = BINARY '".mysql_real_escape_string($TorrentHash)."'");
		if(!$Result){throw new Tracker_Exception(mysql_error());}
		if(mysql_num_rows($Result) > 0){
			return true;
		}else{
			return false;
		}
	}
	
	public static function SetTorrentAuthorised($TorrentHash){
		if(!self::GetTorrentAuthorised($TorrentHash)){
			$Result = mysql_query("INSERT INTO `Authorisation` (`Torrents_Hash`) VALUES ('".mysql_real_escape_string($TorrentHash)."')");
			if(!$Result){throw new Tracker_Exception(mysql_error());}
		}
	}
	
	public static function SetTorrent(Tracker_Torrent $Torrent){
		if(self::GetTorrentExists($Torrent->Hash)){
			$Sql = "UPDATE `Torrents` SET 
						`Torrents_Downloaded` = '".mysql_real_escape_string($Torrent->Downloaded)."',
						`Torrents_Double1` = '".mysql_real_escape_string($Torrent->Double1)."',
						`Torrents_Double2` = '".mysql_real_escape_string($Torrent->Double2)."',
						`Torrents_Double3` = '".mysql_real_escape_string($Torrent->Double3)."',
						`Torrents_Double4` = '".mysql_real_escape_string($Torrent->Double4)."',
						`Torrents_Long1` = '".mysql_real_escape_string($Torrent->Long1)."' 
				    WHERE `Torrents`.`Torrents_Hash` = '".mysql_real_escape_string($Torrent->Hash)."' LIMIT 1";
		}else{
			$Sql = "INSERT INTO `Torrents` 
						(
						`Torrents_Hash` ,
						`Torrents_RawHash` ,
						`Torrents_Added` ,
						`Torrents_Updated` ,
						`Torrents_Downloaded` ,
						`Torrents_Double1` ,
						`Torrents_Double2` ,
						`Torrents_Double3` ,
						`Torrents_Double4` ,
						`Torrents_Long1` 
						)
						VALUES (
						'".mysql_real_escape_string($Torrent->Hash)."', 
						'".mysql_real_escape_string($Torrent->RawHash)."',
						NOW(), 
						NOW(), 
						'0', 
						'".mysql_real_escape_string($Torrent->Double1)."', 
						'".mysql_real_escape_string($Torrent->Double2)."', 
						'".mysql_real_escape_string($Torrent->Double3)."', 
						'".mysql_real_escape_string($Torrent->Double4)."', 
						'".mysql_real_escape_string($Torrent->Long1)."'
						)";
		}
		$Result = mysql_query($Sql);
		try{
			if(!$Result){throw new Tracker_Exception(mysql_error());}
		}catch(Exception $e){
			// ignore
		}
	}
	
	public static function RemoveTorrent(Tracker_Torrent $Torrent){
		$Result = mysql_query("DELETE FROM `Torrents` WHERE `Torrents_Hash` = '".mysql_real_escape_string($Torrent->Hash)."' LIMIT 1");
		if(!$Result){throw new Tracker_Exception(mysql_error());}}
	
	public static function GetPeer($PeerId, $TorrentHash){
		$Result = mysql_query("SELECT * FROM `Peers` WHERE `Peers_Id` = '".mysql_real_escape_string($PeerId)."' AND `Torrents_Hash` = '".mysql_real_escape_string($TorrentHash)."'");
		if(!$Result){throw new Tracker_Exception(mysql_error());}
		if(mysql_num_rows($Result) == 1){
			$Row = mysql_fetch_object($Result);
			return new Tracker_Peer($Row->Peers_Id, $Row->Peers_RawId, $Row->Torrents_Hash, long2ip($Row->Peers_Ip), $Row->Peers_Port, $Row->Peers_Uploaded, $Row->Peers_Downloaded, $Row->Peers_Left);
		}else{
			throw new Tracker_Exception("Peer does not exist");
		}
	}
	
	public static function GetPeerExists($PeerId, $TorrentHash){
		$Result = mysql_query("SELECT * FROM `Peers` WHERE `Peers_Id` = '".mysql_real_escape_string($PeerId)."' AND `Torrents_Hash` = '".mysql_real_escape_string($TorrentHash)."'");
		if(!$Result){throw new Tracker_Exception(mysql_error());}
		if(mysql_num_rows($Result) == 1){
			return true;
		}else{
			return false;
		}
	}
	
	public static function GetPeers(){
		$Peers = array();
		$Result = mysql_query("SELECT * FROM `Peers`");
		if(!$Result){throw new Tracker_Exception(mysql_error());}
		if(mysql_num_rows($Result) > 0){
			while($Row = mysql_fetch_object($Result)){
				$Peers[] = new Tracker_Peer($Row->Peers_Id, $Row->Peers_RawId, long2ip($row->Peers_Ip), $row->Peers_Port, $Row->Peers_Uploaded, $Row->Peers_Left);
			}
		}
		return $Peers;
	}
	
	public static function GetPeerTorrents(Tracker_Peer $Peer){
		$Torrents = array();
		$Result = mysql_query("SELECT * FROM `Torrents` WHERE `Torrents_Hash` IN(SELECT `Torrents_Hash` FROM `Peers` WHERE `Peers`.`Peers_Id` = '".mysql_real_escape_string($Peer->Id)."')");
		if(!$Result){throw new Tracker_Exception(mysql_error());}
		if(mysql_num_rows($Result) > 0){
			while($Row = mysql_fetch_object($Result)){
				$Torrents[] = new Tracker_Torrent($Row->Torrents_Hash, $Row->Torrents_RawHash, $Row->Torrents_Added, $Row->Torrents_Updated, $Row->Torrents_Downloaded, $Row->Torrents_Double1, $Row->Torrents_Double2, $Row->Torrents_Double3, $Row->Torrents_Double4, $Row->Torrents_Long1);
			}
		}
		return $Torrents;
	}
	
	public static function SetPeer(Tracker_Peer $Peer){
		if(self::GetPeerExists($Peer->Id, $Peer->TorrentHash)){
			$Sql = "UPDATE `Peers` SET 
						`Peers_Ip` = '".mysql_real_escape_string(ip2long($Peer->Ip))."',
						`Peers_Port` = '".mysql_real_escape_string($Peer->Port)."',
						`Peers_Uploaded` = '".mysql_real_escape_string($Peer->Uploaded)."',
						`Peers_Downloaded` = '".mysql_real_escape_string($Peer->Downloaded)."',
						`Peers_Left` = '".mysql_real_escape_string($Peer->Left)."' 
					WHERE `Peers_Id` = '".mysql_real_escape_string($Peer->Id)."' AND `Torrents_Hash` = '".mysql_real_escape_string($Peer->TorrentHash)."' LIMIT 1";
		}else{
			$Sql = "INSERT INTO `Peers` (
						`Peers_Id` ,
						`Peers_RawId` ,
						`Torrents_Hash`,
						`Peers_Ip` ,
						`Peers_Port` ,
						`Peers_Added` ,
						`Peers_Updated` ,
						`Peers_Uploaded` ,
						`Peers_Downloaded` ,
						`Peers_Left` 
						)
						VALUES (
						'".mysql_real_escape_string($Peer->Id)."',
						'".mysql_real_escape_string($Peer->RawId)."',
						'".mysql_real_escape_string($Peer->TorrentHash)."', 
						'".mysql_real_escape_string(ip2long($Peer->Ip))."', 
						'".mysql_real_escape_string($Peer->Port)."', 
						NOW(), 
						NOW(), 
						'".mysql_real_escape_string($Peer->Uploaded)."', 
						'".mysql_real_escape_string($Peer->Downloaded)."', 
						'".mysql_real_escape_string($Peer->Left)."'
				   )";
		}
		$Result = mysql_query($Sql);
		try{
			if(!$Result){throw new Tracker_Exception(mysql_error());}
		}catch(Exception $e){
			// ignore
		}
	}
	
	public static function RemovePeer(Tracker_Peer $Peer){
		$Result = mysql_query("DELETE FROM `Peers` WHERE `Peers_Id` = '".mysql_real_escape_string($Peer->Id)."' AND `Torrents_Hash` = '".mysql_real_escape_string($Peer->TorrentHash)."' LIMIT 1");
		if(!$Result){throw new Tracker_Exception(mysql_error());}
	}
	
	public static function Update($TorrentLifeTime, $PeerLifeTime){
		$Result = mysql_query("DELETE FROM `Torrents` WHERE UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`Torrents_Updated`) > $TorrentLifeTime");
		if(!$Result){throw new Tracker_Exception(mysql_error());}
		$Result = mysql_query("DELETE FROM `Peers` WHERE UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`Peers_Updated`) > $PeerLifeTime");
		if(!$Result){throw new Tracker_Exception(mysql_error());}
	}
}
?>
