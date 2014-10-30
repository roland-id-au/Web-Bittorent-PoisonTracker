<?php
class Tracker_ConfigurationSectionBase {
	private $Values;
	
	public function __construct(array $Values){
		$this->Values = $Values;
	}
	
	final public function __get($Key){
		if(array_key_exists($Key, $this->Values)){
			return $this->Values[$Key];
		}else{
			throw new Exception('Class '.get_class($this).' does not contain configuration key \''.$Key.'\'');
		}
	}
	
	final public function GetDelimited($Key, $Delimiter = ',', $SkipEmpty = true, $Trim = true){
		$ValueString = $this->__get($Key);
		$Values = explode($Delimiter, $ValueString);
		if($SkipEmpty){
			$Buffer = array();
			foreach($Values as $Value){
				if($Trim){$Value = trim($Value);}
				if(!empty($Value)){
					$Buffer[] = $Value;
				}
			}
			return $Buffer;
		}elseif($Trim){
			$Buffer = array();
			foreach($Values as $Value){
				$Buffer[] = trim($Value);
			}
			return $Buffer;
		}else{
			return $Values;
		}
	}
	
	public function Load(array $Values){
		$this->Values = array_merge($this->Values, $Values);
	}
}
?>