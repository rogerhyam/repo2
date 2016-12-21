<pre>
<?php

    require_once( '../../../config.php' );

    $user = $_SESSION['repo-tools-username']; // this will come from the session

    // we keep a copy of the last post so the form fields are sticky
    $_SESSION['repo-tools-file-drop'] = $_POST;
    
    $dir_path = "files/$user";
    
    // get the details of what we are importing
    $meta = json_decode(file_get_contents($dir_path . '/_meta.json'));
    
    $file_path = $dir_path . '/' . $meta->file_name;

    // print_r($_POST);
    // print_r($meta);
    
    // we need some way of refering to this thing that will be unique
    // we also need a path in the repository for it
    $now = new DateTime();
    $now_year = $now->format('Y');
    $now_path = $now->format('m/d/H-i-s/');
    $repo_location = "/file_drop/$now_year/$user/$now_path";
    
    // create a document
    $doc = array();

    // make a unique id for it - we all need one of these
    $doc['id'] = 'http://repo.rbge.org.uk/id' . $repo_location . urlencode($meta->file_name); 
    $doc['derivation_rank_i'] = 10;
    
    $doc["storage_location"] = "Repository";
    $doc["storage_location_path"] = $repo_location . $meta->file_name;
    
    $doc['title'] = $_POST['title'];
    $doc['catalogue_number_other'] = $meta->file_name;
    $doc['md5_s'] = $meta->md5;
    
    if(@$_POST['compliance_doc_b']){
        $doc['compliance_doc_b'] = 1;
    }
    
    // created date
    $created = new DateTime($_POST['date']);
    $doc['object_created'] = $created->format('Y-m-d\TH:i:s\Z');
    $doc['object_created_year'] = $created->format('Y');
    
    // creator
    $doc['creator'] = explode(';', $_POST['creator']);
    
    // coll_books_id
    $doc['collector_id_s'] = $_POST['collector_id_s'];
    
    // who and when submitted
    $doc['submitted_by_s'] = $user;
    $doc['submitted_on_dt'] = $now->format('Y-m-d\TH:i:s\Z');
    
    // taxonomy fields
    $doc['family'] = $_POST['family'];
    $doc['genus'] = $_POST['genus'];
    $doc['epithet'] = $_POST['epithet'];

    // keywords
    $doc['keyword_ss'] = $_POST['keyword_ss'];
    
    // geographic stuff
    $doc['country_iso'] = $_POST['country_iso'];
    
    if($_POST['geolocation']){
        $doc['geolocation'] = $_POST['geolocation'];
        $doc['geolocation_accuracy_i'] = $_POST['geolocation_accuracy_i'];   
    }
    
    // look to where we are saving the file and metadata
    $repo_path = REPO_ROOT . $repo_location;
    // make sure it exists
    @mkdir($repo_path, 0777, true);

    // work out the file names for photo and json
    $file_repo_path = $repo_path . $meta->file_name;
    $path_parts = pathinfo($meta->file_name);
    $json_repo_path =  $repo_path . $path_parts['filename'] . '.json';
    

    // JPEG specific stuff
    if($meta->type == 'jpg'){
        
        $doc['item_type'] = 'Image';
        
        $size = getimagesize($file_path);
        $doc['image_width_pixels_i'] = $size[0];        
        $doc['image_height_pixels_i'] = $size[1];
        $doc['mime_type_s'] = $size['mime'];

        // entire exif data as raw data field
        $doc['IGNORE_image_exif'] = file_get_contents($dir_path . '/_exif.json');
        
    }
    
    // PDF specific stuff
    if($meta->type == 'pdf'){
        
        $doc['item_type'] = 'Document';
        // create a higher resolution summary image
        $summary_image_path = $json_repo_path . '.jpg';
        $im = new Imagick();
        $im->setResolution(300,300);
        $im->readimage($file_path . '[0]'); 
        $im->setImageOpacity(1);
        $im->resizeImage(1000,1000,Imagick::FILTER_LANCZOS,1, true);
        $im->setImageFormat('jpeg');
        $im->writeImage($summary_image_path); 
        $im->clear(); 
        $im->destroy();
        
        // if we were successful at creating it add it to the doc
        if(file_exists($summary_image_path)){
            $doc['summary_image_s'] = str_replace(REPO_ROOT, '', $summary_image_path);
        }
        
    }

    // ZIP specific stuff
    if($meta->type == 'zip'){
        // FIXME
    }
    
    // write the json file there
    $out = array();
    $out[] = $doc;
    file_put_contents($json_repo_path, JSON_encode($out, JSON_PRETTY_PRINT));
    
    // copy the file there
    copy($file_path, $file_repo_path);
    
    // check everything is in place and clean up.
    if(file_exists($file_repo_path) && file_exists($json_repo_path)){

        // put it in the list of completed files
        file_put_contents("$dir_path/history.txt", $doc["storage_location_path"] . "\n", FILE_APPEND);

        // remove the stuff from the folder
        if ($dh = opendir($dir_path)) {
          while (($file = readdir($dh)) !== false) {
              if($file == '.') continue;
              if($file == '..') continue;
              if($file == 'history.txt') continue;
              unlink("$dir_path/$file" );
          }
        }
        
        // queue it for re-indexing
        require_once('../../../index/classes/IndexQueue.php');
        $queue = new IndexQueue('edited_items');
        $queue->enqueue($doc['id'], str_replace(REPO_ROOT, '', $json_repo_path));

        // because we may be creating this file as www-data which would prevent the indexer accessing it
        // we make it very permissive - this is a bit hacky as we shouldn't know about the location of the file
        chmod(INDEX_QUEUE_PATH . "/edited_items.db", 0777);

        // send them back to the form
        header("Location: index.php?success=" . urlencode($meta->file_name));
        
    }else{
        header("Location: index.php?failed=" . urlencode($meta->file_name));
    }
    
    // finally add the md5 to the session so we can warn about doing the same file again within this session
    $_SESSION['repo_file_drop_md5s'][$meta->md5] = $meta->file_name;
    
    echo $json_repo_path;
    echo "\n";
    
    print_r($doc);
    

?>
</pre>