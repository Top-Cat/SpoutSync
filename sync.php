<?php

include "objects/artifact.php";
include "objects/build.php";
include "objects/project.php";
include "objects/patch.php";
$workingDir = "/media/raid/web/thomasc.co.uk/mirror/";
$perms = 0770;

function get_text($filename) {
	$ch = curl_init($filename); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$content = curl_exec($ch);
	if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 404) {
		return "e404";
	}
	if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
		return "";
	}
	curl_close($ch);
	return $content;
}

function recurse_delete($str){
        if (is_file($str)){
                return @unlink($str);
        } elseif (is_dir($str)) {
                $scan = glob(rtrim($str,'/').'/*');
                foreach ($scan as $index=>$path){
                        recurse_delete($path);
                }
                return @rmdir($str);
        }
}

$projects = array("Spoutcraft", "Spout", "Vanilla", "SpoutPlugin", "SpoutAPI", "SpoutcraftLauncher");

foreach ($projects as $project) {
	new project($project);
}

$array = json_decode(get_text("http://get.spout.org/patches.php"));
if (!file_exists($workingDir . "patch")) { mkdir($workingDir . "patch", $perms, true); }
foreach ($array as $from => $val) {
	foreach ($val as $to => $md5) {
		new patch($from, $to, $md5);
	}
}

?>