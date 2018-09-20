<?php
	
require_once( '../../../config.php' );
require_once( '../tools_config.php' );

$data_path = REPO_ROOT .  base64_decode($_GET['data_location']);
$direction = $_GET['direction'];

if($direction == 'CCW'){
    $degrees = -90;
}else{
    $degrees = 90;
}

// work out the location of the image file

$data_path = REPO_ROOT .  base64_decode($_GET['data_location']);
$json = json_decode(file_get_contents($data_path));
$doc = $json[0];
$image_file_path = REPO_ROOT . $doc->storage_location_path;

echo $image_file_path;

// rotate the requested file
try{

    // do it using imagemagick so we don't lose exif data
    
    $imagick = new Imagick();
    $imagick->readImage($image_file_path);
    $imagick->rotateImage(new ImagickPixel(), $degrees);
    $imagick->writeImage($image_file_path);

	// remove it from the image cache or we will not see the change.

    $imagick->clear(); 

header('Location: index.php?data_location=' . $_GET['data_location'] . '#tabs-image' );    
 
}catch(Exception $e){
	echo $e->getMessage();
}

	
?>