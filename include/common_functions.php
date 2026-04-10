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
?>