<?php
function display404(){
	header("HTTP/1.0 404 Not Found");
	print "404 innit bruv";
	exit();
}

function getDirectoryFiles(string $path){
	$files = scandir($path);
	$return = array();
	if($files !== false){
		foreach($files as $file){
			if(is_file($path . $file)){
				$return[] = $path . $file;
			}
		}
	}
	return $return;
}

function endsWith(string $haystack, string $needle) {
	return substr_compare($haystack, $needle, -strlen($needle)) === 0;
}

function convertSecondsToHumanReadable(int $seconds){
		$return = array();
		$secs = $seconds % 60;
		$hrs = $seconds / 60;
		$mins = $hrs % 60;
		$hrs = floor($hrs / 60);
		if($hrs > 0){
			$return[] = $hrs . ' hour' . ($hrs > 1 ? 's' : '');
		}
		if($mins > 0){
			$return[] = $mins . ' minute' . ($mins > 1 ? 's' : '');
		}
		$return[] = $secs . ' second' . ($secs > 1 ? 's' : '');
		return implode(' ', $return);
	}
?>