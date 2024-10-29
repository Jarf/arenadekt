<?php
// Vendor Autoload
require_once(DIR_VENDOR . 'autoload.php');
// Common Functions
require_once(DIR_INCLUDE . 'common_functions.php');
// Abstract Classes Autoload
$classes = getDirectoryFiles(DIR_CLASSES_ABSTRACT);
foreach($classes as $class){
    if(endsWith($class, '.php')){
        include_once($class);
    }
}
// Classes Autoload
$classes = getDirectoryFiles(DIR_CLASSES);
foreach($classes as $class){
    if(endsWith($class, '.php')){
        include_once($class);
    }
}
?>