<?php
abstract class Tracker_ConfigurationBase {
	private $Sections;
	
	public function __construct(array $Sections){
		$this->Sections = array();
		foreach($Sections as $Key=>$Section){
			$this->Sections[$Key] = new Tracker_ConfigurationSection($Section);
		}
	}
	
	final public function __get($Key){
		if(array_key_exists($Key, $this->Sections)){
			return $this->Sections[$Key];
		}
	}
	
	final public function Contains(Tracker_Configurationsection $Section){
		return in_array($Section, $this->Sections);
	}
	
	public function Load($Sections){
		foreach($Sections as $Key=>$Section){
			if(array_key_exists($Key, $this->Sections)){
				$this->__get($Key)->Load($Section);
			}
		}
	}
}