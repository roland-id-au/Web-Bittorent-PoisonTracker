<?php
class Tracker_Torrent {
	public $Hash;
	public $RawHash;
	public $Added;
	public $Updated;
	public $Downloaded;
	public $Double1;
	public $Double2;
	public $Double3;
	public $Double4;
	public $Long1;
	
	public function __construct($Hash, $RawHash, $Added, $Updated, $Downloaded, $Double1, $Double2, $Double3, $Double4, $Long1){
		$this->Hash = $Hash;
		$this->RawHash = $RawHash;
		$this->Added = $Added;
		$this->Updated = $Updated;
		$this->Downloaded = $Downloaded;
		$this->Double1 = doubleval($Double1);
		$this->Double2 = doubleval($Double2);
		$this->Double3 = doubleval($Double3);
		$this->Double4 = doubleval($Double4);
		$this->Long1 = intval($Long1);
	}
	
	public function GetAuthorisation(){
		return Tracker_Data::GetTorrentAuthorised($this->Hash);
	}
	
	public function Save(){
		Tracker_Data::SetTorrent($this);
	}
	
	public function __toString(){
		return $this->Hash;
	}
}