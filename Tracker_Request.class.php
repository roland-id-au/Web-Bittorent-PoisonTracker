<?php
abstract class Tracker_Request {
	
	final protected function RequireParameters(){
		$RequiredParameters = func_get_args();
		foreach($RequiredParameters as $RequiredParameter){
			if(!array_key_exists($RequiredParameter, $_REQUEST)){
				throw new Tracker_Exception("Missing request parameter \"$RequiredParameter\"");
			}
		}
	}
	
	protected function MapParameter($Parameter){
		if(array_key_exists($Parameter, $_REQUEST)){
			//if(strlen(urldecode($_REQUEST[$Parameter])) == 20){
			//	return urldecode($_REQUEST[$Parameter]);
			//}else{
			//	return $_REQUEST[$Parameter];
			//}
			return $_REQUEST[$Parameter];
		}else{
			throw new Tracker_Exception("Missing request parameter \"$Parameter\"");
		}
	}
	
	protected function GetParameter($Parameter, $DefaultValue = null){
		if(array_key_exists($Parameter, $_REQUEST)){
			//if(strlen(urldecode($_REQUEST[$Parameter])) == 20){
			//	return urldecode($_REQUEST[$Parameter]);
			//}else{
			//	return $_REQUEST[$Parameter];
			//}
			return $_REQUEST[$Parameter];
		}else if(!is_null($DefaultValue)){
			return $DefaultValue;
		}else{
			throw new Tracker_Exception("Missing request parameter \"$Parameter\"");
		}
	}
	
	final public function GetHash($Parameter){
		return md5($this->__get($Parameter));
	}
	
	final public function __get($Parameter){
		return $this->MapParameter($Parameter);
	}
	
	final public function GetQueryString(){
		return $_SERVER['QUERY_STRING'];
	}
	
	final public function HasParameter($Parameter){
		try{
			$this->MapParameter($Parameter);
		}catch(Exception $e){
			return false;
		}
		return true;
	}
	
	public function GetResponse(){}
	
	public function GetIdentifier(){}
}
?>
