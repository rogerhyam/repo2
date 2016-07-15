<?php
    
    require_once('../config.php');
    
    // image_server.php?kind=200&path=/item_images/accessions/19/02/10/06/Photo_4fdb19b3c7a5b.jpg
    
    // a really simple, one file, image server!
    $within_repo = $_GET['path'];
    $kind = $_GET['kind'];
    $md5 = md5($within_repo);
    
    $cache_dir = "cache/$kind/" . substr($md5, 0,2);
    $cache_path =  "$cache_dir/$md5.jpg";
    
    // if it isn't in the cache create it
    if(!file_exists($cache_path)){
        
        // Set a maximum height and width
        $width = $kind;
        $height = $kind;

        // load the original image
        list($width_orig, $height_orig) = getimagesize(REPO_ROOT . $within_repo);

        $ratio_orig = $width_orig/$height_orig;

        if ($width/$height > $ratio_orig) {
           $width = $height*$ratio_orig;
        } else {
           $height = $width/$ratio_orig;
        }

        $image_p = imagecreatetruecolor($width, $height);
        $image = imagecreatefromjpeg(REPO_ROOT . $within_repo);
        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

        mkdir($cache_dir, 0777, true);
        imagejpeg($image_p, $cache_path, 90);
    
    }
    
    // return the file
    header('Content-Type: image/jpeg');
    readfile($cache_path);

?>