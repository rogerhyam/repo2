<?php

require_once('../config.php');
require_once('../config_bgbase_dump.php');

   $page_size = 1000;
 
   $now = new DateTime();
   echo "Item Image Start: " . $now->format(DATE_ATOM) . "\n";
 
   $offset = 0;
   while(true){
       
       echo "Starting at offset = $offset\n";
       
       // $sql = "SELECT * FROM image_archive.uploaded_images ORDER BY id LIMIT $page_size OFFSET " . $offset;
       $sql = "SELECT * FROM image_archive.uploaded_images WHERE accession_number is NULL ORDER BY id LIMIT $page_size OFFSET " . $offset;
       $response = $mysqli->query($sql);
       echo "\tGot {$response->num_rows}\n";
       
       // give up if we have done them all
       if($response->num_rows == 0) break;
       
       // write out these to files
       while($row = $response->fetch_assoc()){
           process_image($row);
       }
       $offset = $offset + $page_size;
       
       break;

   }
   
   $now = new DateTime();
   echo "Item Image Finish: " . $now->format(DATE_ATOM) . "\n";

   function process_image($row){
       
       // create a document
       $doc = array();
       
       // make a unique id for it - we all need one of these
       // ui for uploaded image
       $doc['id'] = 'http://repo.rbge.org.uk/id/ui/' . $row['id'];
       
       $doc['title'] = $row['original_file_name'];
       
       // the other numbers are the ones we will use to link it to data later
       $doc['catalogue_number'] = $row['barcode_accession'];
       $doc['catalogue_number_other'][] = $row['id'];
       $doc['catalogue_number_other'][] = $row['accession_number'];
       $doc['catalogue_number_other'][] = $row['original_file_name'];
       
       if($row['image_date']){
           $date = new DateTime($row['image_date']);
           $doc['object_created'] = $date->format('Y-m-d\TH:i:s\Z');
           $doc['object_created_year'] = $date->format('Y');
       }

       // creation date if we have one
       $doc['creator'] = $row['photographer'];
   
       // image details as new fields
       $doc['image_height_pixels_i'] = $row['image_pixel_height'];
       $doc['image_width_pixels_i'] = $row['image_pixel_width'];
       $doc['mime_type_s'] = 'image/jpeg';
     
       $doc['submitted_by_s'] = $row['submitted_by'];
       $date = new DateTime($row['created']);
       $doc['submitted_on_dt'] = $date->format('Y-m-d\TH:i:s\Z');
       
       // entire exif data as raw data field
       $doc['IGNORE_image_exif'] = json_decode($row['image_exif']);
       
       // item_type depends on if it is an accession or specimen
       // we store them by type
       $repo_path = REPO_ROOT . '/item_images';
       if($row['accession_number']){
           $doc['item_type'] = 'Accession Photo';
           $path_parts = str_split($row['accession_number'], 2);
           $repo_path .= '/accessions/' . implode('/', $path_parts);
           
           //need to decide if this is attached to a plant or an accession
           // if there is no qualifier in the barcode_accession this is accession linked
           if($row['barcode_accession'] == $row['accession_number']){
               $doc['derived_from'] = 'http://data.rbge.org.uk/living/' . $row['barcode_accession'];
           }else{
               $doc['derived_from'] = 'http://repo.rbge.org.uk/id/plant/' . $row['barcode_accession'];
           }
    
           
       }else{
           $doc['item_type'] = 'Specimen Photo';
           $path_parts = str_split($row['barcode_accession'], 3);
           $repo_path .= '/specimens/' . implode('/', $path_parts);
           
           // we definitely link this to the specimen it is of
           $doc['derived_from'] = 'http://data.rbge.org.uk/herb/' . $row['barcode_accession'];
       
       }
       
       // make sure it exists
       @mkdir($repo_path, 0777, true);
       
       // work out the file names for photo and json
       $image_repo_path = $repo_path . '/'. basename($row['file_path']);
       $json_repo_path =  $repo_path . '/'. basename($row['file_path'], '.jpg') . '.json';
       
       // we record its relative position within the repository as the storage location
       $doc["storage_location"] = "Repository";
       $doc["storage_location_path"] = str_replace(REPO_ROOT, '', $image_repo_path);
       
       // write the json file there
       $out = array();
       $out[] = $doc;
       file_put_contents($json_repo_path, JSON_encode($out));
       
       echo $json_repo_path . "\n";
       
       // write the image file there but only if we don't already have it
       if(!file_exists($image_repo_path)){
           $fetch_url = "http://webed05.rbge.org.uk/rbgimage/get_original.php?mime=image/jpeg&path=" . $row['file_path'];
           file_put_contents($image_repo_path, fopen($fetch_url, 'r'));
           if(file_exists($image_repo_path)){
               echo "Downloaded: $image_repo_path\n";
               
               
           }else{
               echo "Failed! $image_repo_path\n";
           }
       }else{
           echo "Already exists: $image_repo_path\n";
       }

   }

?>