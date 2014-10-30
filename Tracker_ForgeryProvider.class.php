<?php
class Tracker_ForgeryProvider {
	public $MaxFrequencyMultiplier;
	public $MinFrequencyMultiplier;
	
	public function __construct($MaxFrequencyMultiplier, $MinFrequencyMultiplier){
		$this->MaxFrequencyMultiplier = $MaxFrequencyMultiplier;
		$this->MinFrequencyMultiplier = $MinFrequencyMultiplier;
	}
	
	public static function GetRandomPeer($TrustedPeers, &$Id, &$Host, &$Port){
		$Id = "-UT1610-".substr(md5(rand(0,9999999)), 0, 12);
		//$Host = rand(10,255).'.'.rand(10,255).'.'.rand(10,255).'.'.rand(1,254);
		//$Port = rand(6000, 8000);
		if(count($TrustedPeers) > 0){
			$Host = $TrustedPeers[array_rand($TrustedPeers)];
		}else{
			$Host = rand(10,255).'.'.rand(10,255).'.'.rand(10,255).'.'.rand(1,254);
		}
		$Port = 36087;
	}
	
	public function GetNextPeerCount($BaseCount, $MaxAmplitude, $MinAmplitude, &$Double1, &$Double2, &$Double3, &$Double4, &$Long1) {
		//$x = time() % 86400; // x is time seconds the last day started
		$x = time() % 86400;
		$y = 0;
		$y2 = 0;
		if ($Double1 == 0 && $Double2 == 0 && $Double3 == 0 && $Double4 == 0 && $Long1 == 0) {
			// First run
			//$Double1  = mt_rand($this->MinFrequencyMultiplier, $this->MaxFrequencyMultiplier);
			// mt_rand doesn't do decimal/floats
			$Double1 = ($this->MinFrequencyMultiplier + lcg_value() * (abs($this->MaxFrequencyMultiplier - $this->MinFrequencyMultiplier)));
			//$Double1 = 0;
			$Double2  = mt_rand($MinAmplitude, $MaxAmplitude);
			//$Double3  = mt_rand($this->MinFrequencyMultiplier, $this->MaxFrequencyMultiplier);
			// mt_rand doesn't do decimal/floats
			$Double3 = ($this->MinFrequencyMultiplier + lcg_value() * (abs($this->MaxFrequencyMultiplier - $this->MinFrequencyMultiplier)));
			//$Double3 = 0;
			$Double4  = mt_rand($MinAmplitude, $MaxAmplitude);
			$Long1 = time();
		}

		if (time() - $Long1 >= 86400) {
			// its a new day!
			$Double1 = $Double3;
			$Double2 = $Double4;
			$Double3  = ($this->MinFrequencyMultiplier + lcg_value() * (abs($this->MaxFrequencyMultiplier - $this->MinFrequencyMultiplier)));
			//$Double3 = 0;
			$Double4  = mt_rand($MinAmplitude, $MaxAmplitude);
			$Long1 = time();
		}

		$offset = $Double1;
		$high = $Double2;

		//$y = sin($x * (7.27220521664304E-05 + $offset)) * $high;
		$y = sin($x * (7.27220521664304E-05 + $offset)) * $high;
		//$y2 = sin(($x - 86400) * (7.27220521664304E-05 + $Double3)) * $Double4;
		$y2 = sin(($x - 86400) * (7.27220521664304E-05 + $Double3)) * $Double4;
		$y = $y + ($x / 86400) * ($y2 - $y);
		//$y = $y + cos($y * 0.4) * $Double2 / 10;

		if (tan($y) > 0) {
			$y = $y + log10(tan($y) * 100) / log10(3.14159);
		}
		return abs($y + $BaseCount);
	}
	
	public static function GetConfigurationOverride($ConfigurationPath, $Key, &$ConfigurationOverride){
		if(is_readable($ConfigurationPath)){
			$Configuration = parse_ini_file($ConfigurationPath, true);
			if(array_key_exists($Key, $Configuration)){
				$ConfigurationOverride = $Configuration[$Key];
				return true;
			}
		}else{
			throw new Exception('Could not load configuration override');
		}
		return false;
	}
}