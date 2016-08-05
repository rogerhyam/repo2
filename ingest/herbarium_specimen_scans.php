<?php

require_once('../config.php');
require_once('../config_bgbase_dump.php');

   $page_size = 1000;
   //$page_size = 10; // debug
 
    // look to see if we are passed a start date
    if(count($argv) > 1){
        $since = " AND date > '{$argv[1]}'";
        echo "Doing since: {$argv[1]}";
    }else{
        $since = "";
    }
 
   $now = new DateTime();
   echo "Herbarium Specimen Scan Start: " . $now->format(DATE_ATOM) . "\n";
 
   $offset = 0;
   while(true){
       
       echo "Starting at offset = $offset\n";
       
       $sql = "SELECT * FROM image_archive.derived_images WHERE (IMAGE_TYPE = 'JPG' OR IMAGE_TYPE = 'ZOOMIFY') $since ORDER BY `date` ASC LIMIT $page_size OFFSET " . $offset;
       echo $sql . "\n";
       //$sql = "SELECT * FROM image_archive.uploaded_images WHERE accession_number is not NULL ORDER BY id LIMIT $page_size OFFSET " . $offset;
       $response = $mysqli->query($sql);
       echo "\tGot {$response->num_rows}\n";
       
       // give up if we have done them all
       if($response->num_rows == 0) break;
       
       // write out these to files
       while($row = $response->fetch_assoc()){
           process_image($row);
       }
       $offset = $offset + $page_size;
       
       //break; // debug

   }
   
   $now = new DateTime();
   echo "Herbarium Specimen Scan Finish: " . $now->format(DATE_ATOM) . "\n";

   function process_image($row){
       
       // create a document
       $doc = array();
       
       // make a unique id for it - we all need one of these
       // ui for uploaded image
       $doc['id'] = 'http://repo.rbge.org.uk/id/herb_scan/' . $row['ID'];
       $doc['derived_from'] = 'http://data.rbge.org.uk/herb/' . $row['BARCODE'];
       $doc['derivation_rank_i'] = 1;
       $doc['item_type'] = 'Herbarium Specimen Scan';
       
       // link out is the the specimen page with the viewer
       $doc["link_out"] = $doc['derived_from'];
       
       // the other numbers are the ones we will use to link it to data later
       $doc['catalogue_number'] = $row['BARCODE'];
       $doc['catalogue_number_other'][] = $row['ID'];
       $doc['catalogue_number_other'][] = $row['FILENAME'];
       
       if($row['DATE']){
           $date = new DateTime($row['DATE']);
           $doc['object_created'] = $date->format('Y-m-d\TH:i:s\Z');
           $doc['object_created_year'] = $date->format('Y');
       }

       // creation date if we have one
       $doc['creator'] = 'Specimen Digitisation Pipeline';
   
       // image details as new fields
       $doc['image_height_pixels_i'] = $row['IMAGE_PIXEL_WIDTH'];
       $doc['image_width_pixels_i'] = $row['IMAGE_PIXEL_HEIGHT'];

       // work out the paths in the repo
       $repo_path = REPO_ROOT . '/herbarium_specimen_scans/';
       $path_parts = str_split($row['BARCODE'], 3);
       $repo_path .= implode('/', $path_parts);
       $json_repo_path =  $repo_path . '/'. $row['ID'] . '.json';
       $jpeg_repo_path =  $repo_path . '/'. $row['ID'] . '.jpg';
       
       @mkdir($repo_path, 0777, true);
       

       // treat JPG and ZOOMIFY differently
       if($row['IMAGE_TYPE'] == 'JPG'){
          
           $doc['title'] =  'JPEG image file: ' . $row['FILENAME'];
           $doc['mime_type_s'] = 'image/jpeg';
           
           $doc["storage_location"] = "Repository";
           $doc["storage_location_path"] = str_replace(REPO_ROOT, '', $jpeg_repo_path);
           
           // actually move the file (if we need to)
           if(!file_exists($jpeg_repo_path)){
              
              // get path of the file in uber space
              $fetch_url = "http://webed05.rbge.org.uk/rbgimage/get_original.php?mime=image/jpeg&path=" . str_replace('\\', '/', $row['LOCATION']) . '/' . $row['FILENAME'];
              file_put_contents($jpeg_repo_path, fopen($fetch_url, 'r'));
              if(file_exists($jpeg_repo_path)){
                  echo "Downloaded: $jpeg_repo_path\n";
              }else{
                  echo "Failed! $jpeg_repo_path\n";
              }
          }else{
              echo "Already exists: $jpeg_repo_path\n";
          }

       }
       
       if($row['IMAGE_TYPE'] == 'ZOOMIFY' ){
           $doc['title'] =  'Zoomify archive: ' . $row['FILENAME'];
           $doc['mime_type_s'] = 'application/zip';
           
           $doc["storage_location"] = "Specimen Digitisation Pipeline";
           
       }
            
       // write the json file out
       $out = array();
       $out[] = $doc;
       file_put_contents($json_repo_path, JSON_encode($out));
       
       echo $json_repo_path . "\n";
       
   }

?>