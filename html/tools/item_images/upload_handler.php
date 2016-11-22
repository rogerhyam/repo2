<?php
    
/* this is where we handle form posts to the page */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    handleUploads();
}

function handleUploads(){
    
    $user = $_SESSION['repo-tools-username'];
    
    $fileCount = count($_FILES["files"]["name"]);

    for($i = 0; $i < $fileCount; $i++){

        if($_FILES["files"]["type"][$i] != "image/jpeg"){
            echo $_FILES["files"]["name"][$i] . " is not a jpeg - ignoring\n";
            continue;
        }

        $newName = uniqid("Photo_") . ".jpg";

        // check the directory exists
        if(!file_exists("images/$user/")){
            mkdir("images/$user/", 0777, true);
        }

        move_uploaded_file($_FILES["files"]["tmp_name"][$i], "images/$user/" . $newName);
    
        // create a thumbnail 
        // Get new dimensions
        /*
       $percent = 1.0;
       $boundingBoxSize = 200;
       list($width, $height) = getimagesize("images/$user/" . $newName, $info);
   
       if($width > $height){
           $percent = $boundingBoxSize/$width;
       }else{
           $percent = $boundingBoxSize/$height;
       }
       $new_width = $width * $percent;
       $new_height = $height * $percent;

       // Resample
       $image_p = imagecreatetruecolor($new_width, $new_height);
       $image = imagecreatefromjpeg("images/$user/$newName");
       imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
       imagejpeg($image_p, "images/$user/Thumb_$newName");
       */
       
       $imagick = new Imagick("images/$user/$newName");
       $imagick->thumbnailImage(200, 200, true);
       $imagick->writeImage("images/$user/Thumb_$newName"); 
       $imagick->clear();
       
        // we keep the old name in another file
        $out = fopen("images/$user/$newName.txt", 'w');
        fwrite($out,  $_FILES["files"]["name"][$i] . "\n" );
    
        // also keep the date?
        $exif = @exif_read_data("images/$user/" . $newName, 0, true);
        $exifArray = array();
        foreach ($exif as $key => $section) {
            foreach ($section as $name => $val) {
                $exifArray["$key.$name"] = $val;
            }
        }
        if (array_key_exists('EXIF.DateTimeDigitized', $exifArray)){
            fwrite($out,  $exifArray['EXIF.DateTimeDigitized'] . "\n" );
        }elseif(array_key_exists('EXIF.DateTimeOriginal', $exifArray)){
            fwrite($out,  $exifArray['EXIF.DateTimeOriginal'] . "\n" );
        }elseif(array_key_exists('IFD0.DateTime', $exifArray)){
            fwrite($out,  $exifArray['IFD0.DateTime'] . "\n" );
        }else{
            fwrite($out,  "-" . "\n" );
        }
        
        // finally dump the whole exif to the file
        fwrite($out,  json_encode($exif));
            
        fclose($out);        

        //echo $_FILES["files"]["name"][$i] . " saved\n";

    }
}

?>