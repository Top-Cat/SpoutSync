<?php
class patch {
	private $fileName;
	private $md5;

	public function __construct($from, $to, $md5) {
		$this->fileName = "minecraft_" . $from . "-" . $to . ".patch";
		$this->md5 = $md5;

		$this->sync();
	}

	public function getDir() {
		global $workingDir;
		return $workingDir . "patch/" . $this->fileName;
	}

	public function getMD5() {
		//return md5_file($this->getDir());
		$result = explode('= ',exec("/usr/bin/openssl md5 " . $this->getDir())); // Constant time instead of linear time by file size
		return $result[1];
	}

	public function getMD5File() {
		return $this->getDir() . ".md5";
	}

	private function sync() {
		global $perms;
		if (!file_exists($this->getDir()) || $this->md5 != $this->getMD5()) {
			$data = get_text("http://get.spout.org/patch/" . $this->fileName);
			$fh = fopen($this->getDir(), 'w');
			fwrite($fh, $data);
			fclose($fh);

			chmod($this->getDir(), $perms);
		}
	}
}
?>