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
		// Ensure directory exists
		if (!file_exists($this->getDir())) { mkdir($this->getDir(), 0770); }
		
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

		// Do latest and recommended folders
		krsort($this->builds);
		reset($this->builds)->writeToFolder("latest");
		unset($this->folders["latest"]);

		$recommended = get_text('http://build.spout.org/job/' . $this->jenkinsName . '/Stable/buildNumber');
		if (is_numeric($recommended)) {
			if (isset($this->builds[$recommended])) {
				$build = $this->builds[$recommended];
			} else {
				$build = new build($this, $recommended);
			}
			$build->writeToFolder("recommended");
			unset($this->folders["recommended"]);
		}

		// Remove folders that don't relate to builds
		foreach ($this->folders as $folder => $v) {
			recurse_delete($this->getDir() . $folder);
		}
	}
}
?>