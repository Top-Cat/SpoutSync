<?php
class project {
	private $jenkinsName;
	
	public $buildJson;
	private $builds = array();
	private $folders = array();
	
	public function __construct($jenkinsName) {
		$this->jenkinsName = $jenkinsName;
		
		$this->sync();
	}
	
	public function getName() {
		return $this->jenkinsName;
	}
	
	public function getDir() {
		global $workingDir;
		return $workingDir . $this->getName() . "/";
	}

	private function sync() {
		global $perms;
		
		// Ensure directory exists
		if (!file_exists($this->getDir())) { mkdir($this->getDir(), $perms); }
		
		// Find existing synced files
		if ($dh = opendir($this->getDir())) {
			while (($file = readdir($dh)) !== false) {
				if ($file != "." && $file != ".." && is_dir($this->getDir() . '/' . $file)) {
					$this->folders[$file] = 1;
				}
			}
			closedir($dh);
		}

		// Get build info from jenkins
		$this->buildJson = json_decode(get_text("http://build.spout.org/job/" . $this->jenkinsName . "/api/json?tree=lastSuccessfulBuild[number],builds[timestamp,number,result,url,artifacts[relativePath,fileName]]"));
		foreach ($this->buildJson->builds as $build) {
			$this->builds[$build->number] = new build($this, $build);
			unset($this->folders[$build->number]);
		}

		// Remove folders that don't relate to builds
		foreach ($this->folders as $folder => $v) {
			if (intval($folder) !== $folder) {
				recurse_delete($this->getDir() . $folder);
			}
		}
	}
}
?>