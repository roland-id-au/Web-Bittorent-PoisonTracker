<?php
class Tracker_ScrapeRequest extends Tracker_Request {
	private $Tracker;

	public function __construct(Tracker $Tracker){
		$this->Tracker = $Tracker;
		//$this->RequireParameters('info_hash');
	}

	protected function MapParameter($Parameter){
		switch($Parameter){
			case 'info_hash':
				preg_match_all('/info_hash=(.+?)(?:&|\\z){1}/', $_SERVER['QUERY_STRING'], $Matches);
				for($i = 0; $i < count($Matches[1]); $i++){
					if(strlen($Matches[1][$i]) != 20){
						$Matches[1][$i] = urldecode($Matches[1][$i]);
					}
				}
				return $Matches[1];
				break;
			case 'passkey':
				return $this->GetParameter('passkey', '');
				break;
			default:
				return parent::MapParameter($Parameter);
		}
	}

	public function GetResponse(){
		//var_dump($this->info_hash);
		$ForgeryProvider = new Tracker_ForgeryProvider($this->Tracker->Configuration->Forgery->MaximumFrequencyMultiplier, $this->Tracker->Configuration->Forgery->MinimumFrequencyMultiplier);

		$Response = new BEncodedDictionary();
		$Response['files'] = new BEncodedDictionary();

		$InfoHash = $this->info_hash;

		if(is_scalar($InfoHash)){
			$SafeInfoHash = $this->GetHash('info_hash');

			if(Tracker_Data::GetTorrentExists($SafeInfoHash)){
				$Torrent = Tracker_Data::GetTorrent($SafeInfoHash);

				$SeedCount = $ForgeryProvider->GetNextPeerCount($this->Tracker->Configuration->Forgery->BaseSeedCount, $this->Tracker->Configuration->Forgery->MaximumSeedAmplitude, $this->Tracker->Configuration->Forgery->MinimumSeedAmplitude, $Torrent->Double1, $Torrent->Double2, $Torrent->Double3, $Torrent->Double4, $Torrent->Long1);
				$LeechCount = $ForgeryProvider->GetNextPeerCount($this->Tracker->Configuration->Forgery->BaseLeechCount, $this->Tracker->Configuration->Forgery->MaximumLeechAmplitude, $this->Tracker->Configuration->Forgery->MinimumLeechAmplitude, $Torrent->Double3, $Torrent->Double4, $Torrent->Double1, $Torrent->Double2, $Torrent->Long1);

				$Torrent->Save();

				$FakeSeedsRequired = $SeedCount;
				$FakeLeechesRequired = $LeechCount;

				$Response['files'][$InfoHash] = new BEncodedDictionary();
				$Response['files'][$InfoHash]['complete'] = $FakeSeedsRequired;
				$Response['files'][$InfoHash]['downloaded'] = $Torrent->Downloaded + $FakeSeedsRequired;
				$Response['files'][$InfoHash]['incomplete'] = $FakeLeechesRequired;
			}else{
				$Response['files'][$InfoHash] = new BEncodedDictionary();
				$Response['files'][$InfoHash]['complete'] = 6969;
				$Response['files'][$InfoHash]['downloaded'] = 6969;
				$Response['files'][$InfoHash]['incomplete'] = 6969;
			}
		}else{
			if(count($InfoHash) > 0){
				foreach($InfoHash as $Hash){
					$SafeInfoHash = md5($Hash);
					$Response['files'][$Hash] = new BEncodedDictionary();

					if(Tracker_Data::GetTorrentExists($SafeInfoHash)){
						$Torrent = Tracker_Data::GetTorrent($SafeInfoHash);

						$SeedCount = $ForgeryProvider->GetNextPeerCount($this->Tracker->Configuration->Forgery->BaseSeedCount, $this->Tracker->Configuration->Forgery->MaximumSeedAmplitude, $this->Tracker->Configuration->Forgery->MinimumSeedAmplitude, $Torrent->Double1, $Torrent->Double2, $Torrent->Double3, $Torrent->Double4, $Torrent->Long1);
						$LeechCount = $ForgeryProvider->GetNextPeerCount($this->Tracker->Configuration->Forgery->BaseLeechCount, $this->Tracker->Configuration->Forgery->MaximumLeechAmplitude, $this->Tracker->Configuration->Forgery->MinimumLeechAmplitude, $Torrent->Double3, $Torrent->Double4, $Torrent->Double1, $Torrent->Double2, $Torrent->Long1);

						$Torrent->Save();

						$FakeSeedsRequired = $SeedCount;
						$FakeLeechesRequired = $LeechCount;

						$Response['files'][$Hash] = new BEncodedDictionary();
						$Response['files'][$Hash]['complete'] = $FakeSeedsRequired;
						$Response['files'][$Hash]['downloaded'] = $Torrent->Downloaded + $FakeSeedsRequired;
						$Response['files'][$Hash]['incomplete'] = $FakeLeechesRequired;
					}else{
						$TempDouble1 = 0;
						$TempDouble2 = 0;
						$TempDouble3 = 0;
						$TempDouble4 = 0;
						$TemplLong1 = 0;
						$SeedCount = $ForgeryProvider->GetNextPeerCount($this->Tracker->Configuration->Forgery->BaseSeedCount, $this->Tracker->Configuration->Forgery->MaximumSeedAmplitude, $this->Tracker->Configuration->Forgery->MinimumSeedAmplitude, $TempDouble1, $TempDouble2, $TempDouble3, $TempDouble4, $TemplLong1);
						$LeechCount = $ForgeryProvider->GetNextPeerCount($this->Tracker->Configuration->Forgery->BaseLeechCount, $this->Tracker->Configuration->Forgery->MaximumLeechAmplitude, $this->Tracker->Configuration->Forgery->MinimumLeechAmplitude, $TempDouble1, $TempDouble2, $TempDouble3, $TempDouble4, $TemplLong1);
						$FakeSeedsRequired = $SeedCount;
						$FakeLeechesRequired = $LeechCount;

						$Response['files'][$Hash] = new BEncodedDictionary();
						$Response['files'][$Hash]['complete'] = 6969;//$FakeSeedsRequired;
						$Response['files'][$Hash]['downloaded'] = 6969;//$FakeSeedsRequired;
						$Response['files'][$Hash]['incomplete'] = 6969;//$FakeLeechesRequired;
					}
				}
			}else if($this->Tracker->Configuration->Tracker->AllowFullScrape){
				$Torrents = Tracker_Data::GetTorrents();
				$Buffer = array();
				foreach($Torrents as $Torrent){
					if(!$this->Tracker->Configuration->Tracker->AllowAnonymous && !$Torrent->GetAuthorisation()){
						continue;
					}

					$SeedCount = $ForgeryProvider->GetNextPeerCount($this->Tracker->Configuration->Forgery->BaseSeedCount, $this->Tracker->Configuration->Forgery->MaximumSeedAmplitude, $this->Tracker->Configuration->Forgery->MinimumSeedAmplitude, $Torrent->Double1, $Torrent->Double2, $Torrent->Double3, $Torrent->Double4, $Torrent->Long1);
					$LeechCount = $ForgeryProvider->GetNextPeerCount($this->Tracker->Configuration->Forgery->BaseLeechCount, $this->Tracker->Configuration->Forgery->MaximumLeechAmplitude, $this->Tracker->Configuration->Forgery->MinimumLeechAmplitude, $Torrent->Double3, $Torrent->Double4, $Torrent->Double1, $Torrent->Double2, $Torrent->Long1);

					//$Torrent->Save();

					$FakeSeedsRequired = $SeedCount;
					$FakeLeechesRequired = $LeechCount;

					$ResponseBuffer = new BEncodedDictionary();
					$ResponseBuffer['complete'] = $FakeSeedsRequired;
					$ResponseBuffer['downloaded'] = $FakeSeedsRequired;
					$ResponseBuffer['incomplete'] = $FakeLeechesRequired;
					$Buffer[] = strlen($Torrent->RawHash).':'.$Torrent->RawHash.$ResponseBuffer->Encode();

					//$Response['files'][$Torrent->RawHash] = new BEncodedDictionary();
					//$Response['files'][$Torrent->RawHash]['complete'] = $FakeSeedsRequired;
					//$Response['files'][$Torrent->RawHash]['downloaded'] = $Torrent->Downloaded + $FakeSeedsRequired;
					//$Response['files'][$Torrent->RawHash]['incomplete'] = $FakeLeechesRequired;
					//break;
				}
				return 'd5:filesd'.implode('', $Buffer).'ee';
			}else{
				throw new Exception('Full scrape is not permitted');
			}
		}
		return $Response->Encode();
	}

	public function GetIdentifier(){
		$Identifier = __CLASS__.$this->passkey;
		if(is_array($this->info_hash)){
			$Identifier .= implode(null, $this->info_hash);
		}else{
			$Idenfier .= $this->info_hash;
		}
		return md5($Identifier);
	}
}
?>
