<?php
class Tracker_Configuration extends Tracker_ConfigurationBase {
	public function __construct($IniPath){
		if(is_readable($IniPath)){
			$Sections = parse_ini_file($IniPath, true);
			$Buffer = array();
			foreach($Sections as $Key=>$Section){
				$Buffer[ucfirst($Key)] = $Section;
			}
			parent::__construct($Buffer);
		}
	}
}