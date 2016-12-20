<?php

    // accepts the post of a file from the form
    
    // all we need is their handle
    require_once( '../../../config.php' );
    $user = $_SESSION['repo-tools-username'];
 
    // we gather information about the file into this and then cache it as a file
    $meta = array();
    
    // firstly clear the users cache directory of any existing data
    $dir_path = "files/$user";
    if(file_exists($dir_path)){
          if ($dh = opendir($dir_path)) {
                while (($file = readdir($dh)) !== false) {
                    if($file == '.') continue;
                    if($file == '..') continue;
                    if($file == 'history.txt') continue;
                    unlink("$dir_path/$file" );
                }
          }
    }else{
        mkdir($dir_path);
    }
    
    // put the file in the directory
    $file_path = "$dir_path/" . $_FILES["file"]["name"];
    move_uploaded_file($_FILES["file"]["tmp_name"], $file_path);
    
    // get the md5 for it so we can track duplicates
    $meta['md5'] = md5_file($file_path);
    
    // it depends what it is
    $file_mime = mime_content_type($file_path);
    
    // create a thumbnail
    switch ($file_mime) {
        case 'image/jpeg':
            create_jpeg_thumb($dir_path, $file_path);
            create_jpeg_meta($dir_path, $file_path, $_FILES["file"]["name"]);
            break;
        case 'application/pdf':
            create_pdf_thumb($dir_path, $file_path);
            create_pdf_meta($dir_path, $file_path, $_FILES["file"]["name"]);
            break;
        
        default:
            echo "Unknown mime type: " . $file_mime;
            exit;
    }
   
    // write the meta to file to be picked up later
    file_put_contents($dir_path . '/_meta.json', json_encode($meta, JSON_PRETTY_PRINT));

    // send them back to the form
    header("Location: index.php?duplicate=" . urlencode(likely_duplicate_file($meta['md5'])));
    exit;
    
    function create_jpeg_thumb($dir_path, $file_path){
         $imagick = new Imagick($file_path);
         auto_rotate_image($imagick);
         $imagick->thumbnailImage(350, 350, true);
         $imagick->writeImage($dir_path . '/_thumb.jpg'); 
         $imagick->clear();
    }
    
    function create_jpeg_meta($dir_path, $file_path, $file_name){
        
        global $meta;
        
        $meta['type'] = 'jpg';
        $meta['title'] = trim(str_replace(array('_','.','-','jpeg', 'jpg', 'pdf', 'JPEG', 'JPG', 'PDF'), ' ', $file_name));
        $meta['file_name'] = $file_name;
        
        $exif = @exif_read_data($file_path, 0, true);
        
        $meta['date'] = get_date_from_exif($exif);
        $meta['coordinates'] = get_gps_from_exif(@$exif['GPS']);
        
        file_put_contents($dir_path . '/_exif.json', json_encode($exif, JSON_PRETTY_PRINT));
    }
    
    function create_pdf_thumb($dir_path, $file_path){
        $im = new Imagick();
        $im->setResolution(300,300);
        $im->readimage($file_path . '[0]'); 
        $im->setImageOpacity(1);
        $im->resizeImage(350,350,Imagick::FILTER_LANCZOS,1, true);
        $im->setImageFormat('jpeg');
        $im->writeImage($dir_path . '/_thumb.jpg'); 
        $im->clear(); 
        $im->destroy();
    }
    
    function create_pdf_meta($dir_path, $file_path, $file_name){
        
        global $meta;
        
        $meta['type'] = 'pdf';
        $meta['file_name'] = $file_name;
         
         
        // we can call SOLR to parse out the file

        $full_path = realpath($file_path);
        $uri = REPO_SOLR_URI . '/update/extract?extractOnly=true&wt=json&indent=true';
        $args['file'] = new CurlFile($full_path, 'application/pdf');

         $ch = curl_init( $uri );
         curl_setopt($ch, CURLOPT_POST,1);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
         curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
         $result = curl_exec($ch);
         curl_close($ch);

         $result = json_decode($result);

         file_put_contents($dir_path . '/_extract.json', json_encode($result, JSON_PRETTY_PRINT));


         // was it successful
         $meta_path = $full_path . '_metadata';
         if($result->responseHeader->status == 0 && isset($result->$meta_path)){
             
             $pdf_metadata = $result->$meta_path;
             
             // it is striped name then value
             for ($i=0; $i < count($pdf_metadata); $i = $i + 2) { 
                $key = $pdf_metadata[$i];
                $val = $pdf_metadata[$i + 1];
                
                if($key == 'dc:creator') $meta['creator'] = implode(';', $val);
                if($key == 'dc:title') $meta['title'] = implode(';', $val);
                if($key == 'dc:description') $meta['description'] = implode(';', $val);
                
                
                if($key == 'dcterms:created' && count($val)) $meta['date'] = substr($val[0], 0, 10);

             }
             
         }

        if(!@$meta['title']){
            $meta['title'] = trim(str_replace(array('_','.','-','jpeg', 'jpg', 'pdf', 'JPEG', 'JPG', 'PDF'), ' ', $file_name));
        }
        
        
        
    }
    
    function get_date_from_exif($exif){
        
        $exifArray = array();
        
        $date = false;
        
        foreach ($exif as $key => $section) {
            foreach ($section as $name => $val) {
                $exifArray["$key.$name"] = $val;
            }
        }
        
        if (array_key_exists('EXIF.DateTimeDigitized', $exifArray)){
            $date = $exifArray['EXIF.DateTimeDigitized'];
        }
        
        if(array_key_exists('EXIF.DateTimeOriginal', $exifArray)){
           $date = $exifArray['EXIF.DateTimeOriginal'];
        }
        
        if(array_key_exists('IFD0.DateTime', $exifArray)){
            $date = $exifArray['IFD0.DateTime'];
        }
        
        if($date){
            $date = substr($date, 0, 10);
            $date = str_replace(':', '-', $date);
        }
        
        return $date;
        
    }
    
    // from here - http://stackoverflow.com/questions/5449282/reading-geotag-data-from-image-in-php
    function get_gps_from_exif($info){

        if (isset($info['GPSLatitude']) && isset($info['GPSLongitude']) &&
            isset($info['GPSLatitudeRef']) && isset($info['GPSLongitudeRef']) &&
            in_array($info['GPSLatitudeRef'], array('E','W','N','S')) && in_array($info['GPSLongitudeRef'], array('E','W','N','S'))) {

            $GPSLatitudeRef  = strtolower(trim($info['GPSLatitudeRef']));
            $GPSLongitudeRef = strtolower(trim($info['GPSLongitudeRef']));

            $lat_degrees_a = explode('/',$info['GPSLatitude'][0]);
            $lat_minutes_a = explode('/',$info['GPSLatitude'][1]);
            $lat_seconds_a = explode('/',$info['GPSLatitude'][2]);
            $lng_degrees_a = explode('/',$info['GPSLongitude'][0]);
            $lng_minutes_a = explode('/',$info['GPSLongitude'][1]);
            $lng_seconds_a = explode('/',$info['GPSLongitude'][2]);

            $lat_degrees = $lat_degrees_a[0] / $lat_degrees_a[1];
            $lat_minutes = $lat_minutes_a[0] / $lat_minutes_a[1];
            $lat_seconds = $lat_seconds_a[0] / $lat_seconds_a[1];
            $lng_degrees = $lng_degrees_a[0] / $lng_degrees_a[1];
            $lng_minutes = $lng_minutes_a[0] / $lng_minutes_a[1];
            $lng_seconds = $lng_seconds_a[0] / $lng_seconds_a[1];

            $lat = (float) $lat_degrees+((($lat_minutes*60)+($lat_seconds))/3600);
            $lng = (float) $lng_degrees+((($lng_minutes*60)+($lng_seconds))/3600);

            //If the latitude is South, make it negative. 
            //If the longitude is west, make it negative
            $GPSLatitudeRef  == 's' ? $lat *= -1 : '';
            $GPSLongitudeRef == 'w' ? $lng *= -1 : '';

            return "$lat,$lng";
            
        }
        return false;
    }
    
    function auto_rotate_image($image) {
        $orientation = $image->getImageOrientation();

        switch($orientation) {
            case imagick::ORIENTATION_BOTTOMRIGHT: 
                $image->rotateimage("#000", 180); // rotate 180 degrees
                break;

            case imagick::ORIENTATION_RIGHTTOP:
                $image->rotateimage("#000", 90); // rotate 90 degrees CW
                break;

            case imagick::ORIENTATION_LEFTBOTTOM: 
                $image->rotateimage("#000", -90); // rotate 90 degrees CCW
                break;
        }

        // Now that it's auto-rotated, make sure the EXIF data is correct in case the EXIF gets saved with the image!
        $image->setImageOrientation(imagick::ORIENTATION_TOPLEFT);
    }
    
    function likely_duplicate_file($md5){
        
        // if we have it from this session return it
        if(isset($_SESSION['repo_file_drop_md5s'][$md5])){
            return $_SESSION['repo_file_drop_md5s'][$md5];
        }
        
        // check if we have anything in the index with that md5
        $query = "md5_s:\"$md5\"";
        $uri = REPO_SOLR_URI . '/query?q=' . urlencode($query) . '&rows=20';
        $ch = curl_init( $uri );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        // Send request.
        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result);

        $out = array();

        if($result->responseHeader->status == 0 && isset($result->response->docs) && count($result->response->docs) > 0){
            $title = $result->response->docs[0]->title[0];
            $uri = "/index.php?q=md5_s:$md5&repo_type=complex&" . REPO_SOLR_QUERY_STRING;
            return "<a href=\"$uri\">$title</a>";
        }
        
        return false;
    
    }
    
?>