<?php
class Tracker_Cache {
	private $Memcache;
	
	public function __construct($MemcacheHostname, $MemcachePort, $MemcachePersistant = false){
		$this->Memcache = new Memcache();
		if($MemcachePersistant){
			if(!$this->Memcache->pconnect($MemcacheHostname, $MemcachePort, 5)){
				throw new Tracker_Exception("Memcache connection failed");
			}
		}else{
			if(!$this->Memcache->connect($MemcacheHostname, $MemcachePort, 5)){
				throw new Tracker_Exception("Memcache connection failed");
			}
		}
	}
	
	public function GetCache($Identifier, &$CachedData){
		$CachedData = $this->Memcache->get($Identifier);
		if($CachedData == false){
			return false;
		}else{
			return true;
		}
	}
	
	public function SetCache($Identifier, $CachedData, $LifeTime){
		$this->Memcache->set($Identifier, $CachedData, 0, $LifeTime);
	}
	
	public function UpdateCache($Identifier, $CachedData, $LifeTime){
		$this->Memcache->replace($Identifier, $CachedData, 0, $LifeTime);
	}
}
?>