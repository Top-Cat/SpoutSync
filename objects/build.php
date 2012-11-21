<?php
class build {
	private $project;
	private $buildNo;
	private $result;
	private $artifacts;
	private $timestamp;
	private $url;
	
	public function __construct($project, $buildInfo) {
		global $workingDir;
		$this->project = $project;
		$this->buildNo = $buildInfo->number;
		$this->result = $buildInfo->result;
		$this->artifacts = $buildInfo->artifacts;
		$this->timestamp = $buildInfo->timestamp;
		$this->url = $buildInfo->url;
		
		$this->sync();
	}
	
	public function getDir() {
		return $this->project->getDir() . $this->buildNo . "/";
	}
	
	public function getTimestamp() {
		return $this->timestamp;
	}
	
	public function getUrl() {
		return $this->url;
	}

	public function writeToFolder($folder = "recommended") {
		$this->buildNo = $folder;
		$this->sync(1);
	}
	
	private function sync($force = 0) {
		global $perms;
		if ($this->result == "FAILURE") {
			@rmdir($this->getDir());
			return;
		}
		
		if (!file_exists($this->getDir())) { mkdir($this->getDir(), $perms); }
	
		foreach ($this->artifacts as $artifact) {
			new artifact($this, $artifact, $force);
		}
	}
}
?>