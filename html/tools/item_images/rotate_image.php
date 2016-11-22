<?php
 
    session_start();
 
    $username = $_SESSION['repo-tools-username'];
    $fileName =  $_GET['id'];
    $direction = $_GET['direction'];
 
    $response = array();
    $response['errors'] = false;
    $response['message'] =  $_GET['id'];
 
 
    if($direction == 'LEFT'){
        $degrees = -90;
    }else{
        $degrees = 90;
    }

    // rotate the requested file
    try{

        // do it using imagemagick so we don't lose exif data
        
        $imagick = new Imagick();
        $imagick->readImage("images/$username/$fileName");
        $imagick->rotateImage(new ImagickPixel(), $degrees);
        $imagick->writeImage("images/$username/$fileName");

        $imagick->readImage("images/$username/Thumb_$fileName");
        $imagick->rotateImage(new ImagickPixel(), $degrees);
        $imagick->writeImage("images/$username/Thumb_$fileName"); 

        $imagick->clear(); 

        
        /*

        $mainImage = imagecreatefromjpeg("images/$username/$fileName");
        $rotated = imagerotate($mainImage, $degrees, 0);
        imagejpeg($rotated, "images/$username/$fileName");
        imagedestroy($mainImage);
        imagedestroy($rotated);
    
        $thumbnail = imagecreatefromjpeg("images/$username/Thumb_$fileName");
        $rotated = imagerotate($thumbnail, $degrees, 0);
        imagejpeg($rotated, "images/$username/Thumb_$fileName");
        imagedestroy($thumbnail);
        imagedestroy($rotated);
     
        */
     
    }catch(Exception $e){
        $response['errors'] = true;
        $response['message'] = $e->getMessage();
    }
    
    header('Content-Type: text/javascript');
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');
    echo json_encode($response);
    exit();
    
?>