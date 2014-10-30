<?php
class Tracker_Peer {
	public $Id;
	public $RawId;
	public $TorrentHash;
	public $Ip;
	public $Port;
	public $Added;
	public $Updated;
	public $Uploaded;
	public $Downloaded;
	public $Left;
	
	public function __construct($Id, $RawId, $TorrentHash, $Ip, $Port, $Uploaded, $Downloaded, $Left){
		$this->Id = $Id;
		$this->RawId = $RawId;
		$this->TorrentHash = $TorrentHash;
		$this->Ip = $Ip;
		$this->Port = intval($Port);
		$this->Uploaded = intval($Uploaded);
		$this->Downloaded = intval($Downloaded);
		$this->Left = intval($Left);	
	}
	
	public function Save(){
		Tracker_Data::SetPeer($this);
	}
	
	public function __toString(){
		return $this->Id;
	}
}
?>