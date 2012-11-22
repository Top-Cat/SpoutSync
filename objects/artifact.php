<?php
class artifact {
	private $build;
	private $fileName;
	private $relativePath;
	private $force;
	
	public function __construct($build, $artifactInfo, $force) {
		$this->build = $build;
		$this->fileName = $artifactInfo->fileName;
		$this->relativePath = $artifactInfo->relativePath;
		$this->force = $force;
		
		$this->sync();
	}
	
	public function getDir() {
		return $this->build->getDir() . $this->fileName;
	}

	public function getMD5() {
		//return md5_file($this->getDir());
		$result = explode('= ',exec("/usr/bin/openssl md5 " . $this->getDir())); // Constant time instead of linear time by file size
		return $result[1];
	}

	public function saveMD5() {
		$jdata = get_text($this->build->getUrl() . "artifact/" . $this->relativePath . '/*fingerprint*/');
		if ($jdata != "e404") {
			preg_match("/MD5: ([a-zA-Z0-9]{32})/", $jdata, $matches);
			if (sizeof($matches) > 0) {
				file_put_contents($this->getMD5File(), $matches[1]);
			}
		}
	}

	public function getMD5File() {
		return $this->getDir() . ".md5";
	}

	public function getCachedMD5() {
		return file_exists($this->getMD5File()) ? file_get_contents($this->getMD5File()) : "";
	}
	
	private function sync() {
		global $perms;
		if (!file_exists($this->getDir()) || $this->getCachedMD5() != $this->getMD5() || $this->force) {
			$data = get_text($this->build->getUrl() . "artifact/" . $this->relativePath);
			$fh = fopen($this->getDir(), 'w');
			fwrite($fh, $data);
			fclose($fh);

			$this->saveMD5();
		
			chmod($this->getDir(), $perms);
			touch($this->build->getDir(), $this->build->getTimestamp() / 1000);
			touch($this->getDir(), $this->build->getTimestamp() / 1000);
		}
	}
}
?>