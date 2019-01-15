<?php
   
    require_once('../config.php');
    require_once('inc/functions.php');
        
    // image_server.php?kind=200&path=/item_images/accessions/19/02/10/06/Photo_4fdb19b3c7a5b.jpg
    
    // a really simple, one file, image server!
    $within_repo = @$_GET['path'];
    
    // we may be passed a base64 encoded path
    if(@$_GET['path_base64']){
        $within_repo = trim(base64_decode($_GET['path_base64']));
    }
    
    $kind = @$_GET['kind'];
    if(!$kind) $kind = 600;
    $md5 = md5($within_repo);
	
	// are we forcing the regeneration of the cached files associated with this
	// image - all kinds (sizes)
	if(@$_GET['force_refresh']){
		
		// work through the dirs in cache - one for each kind
		$directories = glob('cache/*', GLOB_ONLYDIR);
		foreach($directories as $dir){
			$path = $dir . '/' . substr($md5, 0,2) . '/' . $md5 . '.jpg';
			if(file_exists($path)) unlink($path);
		}
	}
    
	// create the path for this kind
    $cache_dir = "cache/$kind/" . substr($md5, 0,2);
    $cache_path =  "$cache_dir/$md5.jpg";
	
    // if it isn't in the cache create it
    if(!file_exists($cache_path)){
        
        $src_path = REPO_ROOT . $within_repo;
        
        // parse out the size if needed
        if(is_numeric($kind)){
            make_smaller_file($src_path, $cache_path, $kind);
        }else if ($kind == 'original'){
            make_original_file($src_path, $cache_path, $within_repo);
        }else{
            
            // something like 100-square 
            list($size, $version) = explode('-', $kind);
            $func_name = "make_" . $version . "_file";
            $func_name($src_path, $cache_path, $size);

        }
    
    }

    // return the file
    header('Content-Type: image/jpeg');
    header("Content-length: " . filesize($cache_path));
	header("Access-Control-Allow-Origin: *");
    readfile($cache_path);
    exit;
    
    
    function make_original_file($src_path, $dest_path, $within_repo){
        
        $doc = get_doc_for_file_path($within_repo);
        if(!$doc){
            header("HTTP/1.0 500 Internal Server Error");
            echo "Error retrieving metadata for file.";
            exit;
        }    

        if(doc_is_embargoed($doc)){
            header("HTTP/1.0 403 Forbidden");
            echo "This file is embargoed until " . $embargo_date->format('Y-m-d');
            exit;
        }
        
        $path_parts = pathInfo($dest_path); 
        @mkdir($path_parts['dirname'], 0777, true);
        
        copy($src_path, $dest_path);
        
    }
    
    function make_smaller_file($src_path, $dest_path, $max_dimension){
        
        // Set a maximum height and width
        $width = $max_dimension;
        $height = $max_dimension;

        // load the original image
        list($width_orig, $height_orig) = getimagesize($src_path);

        // only resize smaller - otherwize return max
        if($width_orig > $width || $height_orig > $height){
            
            $ratio_orig = $width_orig/$height_orig;

            if ($width/$height > $ratio_orig) {
               $width = $height*$ratio_orig;
            } else {
               $height = $width/$ratio_orig;
            }
            
        }else{
            $width = $width_orig;
            $height = $height_orig;
        }

        
        //where we will put it 
        $path_parts = pathInfo($dest_path); 
        @mkdir($path_parts['dirname'], 0777, true);
        
        // Imagick way
        $thumb = new Imagick($src_path);
        $thumb->resizeImage($width,$height,Imagick::FILTER_LANCZOS,1);
        auto_rotate_image($thumb);
        $thumb->writeImage($dest_path);
        $thumb->destroy();


        
    }

    function make_square_file($scrFile, $thumbFile, $thumbSize){
      
        // make a home for it
        $path_parts = pathInfo($thumbFile); 
        @mkdir($path_parts['dirname'], 0777, true);
      
        $imagick = new Imagick($scrFile);
        auto_rotate_image($imagick);
        $imagick->cropThumbnailImage($thumbSize, $thumbSize, true);
        $imagick->writeImage($thumbFile); 
        $imagick->clear();
      
        /*
      
        $src = imagecreatefromjpeg( $scrFile );

        // Determine the Image Dimensions
        $oldW = imagesx( $src );
        $oldH = imagesy( $src );

        // Calculate the New Image Dimensions 
        $limiting_dim = 0;
        if( $oldH > $oldW ){
            // Portrait 
            $limiting_dim = $oldW;
        }else{
            // Landscape 
            $limiting_dim = $oldH;
        }

        $new = imagecreatetruecolor( $thumbSize , $thumbSize );
        imagecopyresampled( $new , $src , 0 , 0 , ($oldW-$limiting_dim )/2 , ( $oldH-$limiting_dim )/2 , $thumbSize , $thumbSize , $limiting_dim , $limiting_dim );
        
        $path_parts = pathInfo($thumbFile); 
        @mkdir($path_parts['dirname'], 0777, true);
        
        imagejpeg( $new , $thumbFile, 90);

        imagedestroy( $new );
        imagedestroy( $src );
        
        */

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
    

?>