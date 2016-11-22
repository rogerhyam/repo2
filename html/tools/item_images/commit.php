<?php

   require_once( '../../../config.php' );

   $ajaxResponse = array();
   $ajaxResponse['errors'] = false;
   $ajaxResponse['message'] = "Saved: ";
   
   $user = $_SESSION['repo-tools-username']; // this will come from the session
   $barcodeAccession = $_REQUEST['id'];
   $photographer = $_REQUEST['photographer'];
   
   // work through the images in the directory
   if ($dh = opendir("images/$user")) {

       while (($file = readdir($dh)) !== false) {
           
           // only look at the main jpegs - others will be calculated
           if(!preg_match('/^Photo_[a-z0-9]+\.jpg$/', $file) ) continue;
           
           // we get other data out of the metadata file
            $meta = file("images/$user/$file.txt");
            $originalFileName = false;
            $imageDate = false;
            $imageExif = '';
            foreach($meta as $line){

              // first line is file name
              if(!$originalFileName){
                  $originalFileName = trim($line);
                  continue;
              }

              // second line is date or -
              if(!$imageDate){
                  if(trim($line) == '-'){
                      $imageDate = '-';
                  }else{
                      $imageDate = str_replace(':', '-', substr($line, 0, 10 ) );
                  }
                  continue;
              }

              // rest of lines are exif data dump
              $imageExif .= $line;

            }
           
                  
             // create a document
            $doc = array();

            // make a unique id for it - we all need one of these
            $doc['id'] = 'http://repo.rbge.org.uk/id/ui/' . $file; // already have unique id as file name
            $doc['title'] = $originalFileName;

            // the other numbers are the ones we will use to link it to data later
            $doc['catalogue_number'] = $barcodeAccession; // fixme 
            $doc['catalogue_number_other'][] = $barcodeAccession;
            $doc['catalogue_number_other'][] = $originalFileName;
            $doc['catalogue_number_other'][] = $file;

            if($imageDate && $imageDate != '-'){
              $date = new DateTime($imageDate);
              $doc['object_created'] = $date->format('Y-m-d\TH:i:s\Z');
              $doc['object_created_year'] = $date->format('Y');
            }

            // creation date if we have one
            $doc['creator'] = $photographer;

            // image details as new fields
            $size = getimagesize("images/$user/$file");
            $doc['image_width_pixels_i'] = $size[0];        
            $doc['image_height_pixels_i'] = $size[1];
            $doc['mime_type_s'] = $size['mime'];

            $doc['submitted_by_s'] = $user;
            $date = new DateTime();
            $doc['submitted_on_dt'] = $date->format('Y-m-d\TH:i:s\Z');

            // entire exif data as raw data field
            $doc['IGNORE_image_exif'] = $imageExif;

            // item_type depends on if it is an accession or specimen
            // we store them by type
            $repo_path = REPO_ROOT . '/item_images';
            if(preg_match('/^E[0-9]{8}/i', $barcodeAccession)){
                
                // this is a photo of a herbarium specimen
                $doc['item_type'] = 'Specimen Photo';
                $path_parts = str_split($barcodeAccession, 3);
                $repo_path .= '/specimens/' . implode('/', $path_parts);

                // we definitely link this to the specimen it is of
                $doc['derived_from'] = 'http://data.rbge.org.uk/herb/' . $barcodeAccession;

            }else{

                // this is a photo of a garden accession or plant
                $doc['item_type'] = 'Accession Photo';
                $path_parts = str_split($barcodeAccession, 2);
                $repo_path .= '/accessions/' . implode('/', $path_parts);

                // link it to the plant/accession
                $doc['derived_from'] = 'http://data.rbge.org.uk/living/' . $barcodeAccession;

            }

            $doc['derivation_rank_i'] = 10;

            // make sure it exists
            @mkdir($repo_path, 0777, true);

            // work out the file names for photo and json
            $image_repo_path = $repo_path . '/'. basename($file);
            $json_repo_path =  $repo_path . '/'. basename($file, '.jpg') . '.json';

            // we record its relative position within the repository as the storage location
            $doc["storage_location"] = "Repository";
            $doc["storage_location_path"] = str_replace(REPO_ROOT, '', $image_repo_path);

            // write the json file there
            $out = array();
            $out[] = $doc;
            file_put_contents($json_repo_path, JSON_encode($out, JSON_PRETTY_PRINT));

            //echo $json_repo_path . "\n";

            // write the image file there but only if we don't already have it
            if(!file_exists($image_repo_path)){
                copy("images/$user/$file", $image_repo_path);
            }else{
              $ajaxResponse['message'] .= "\nAlready exists: $image_repo_path";
            }
         
           // check everything is in place and clean up.
           if(file_exists($image_repo_path) && file_exists($json_repo_path)){
               file_put_contents("images/$user/history.txt", $doc["storage_location_path"] . "\n", FILE_APPEND);
               $ajaxResponse['message'] .= "\n" . $originalFileName;
               unlink("images/$user/$file");
               unlink("images/$user/$file.txt");
               unlink("images/$user/Thumb_$file");
           }
           
           // queue it for indexing
           $data_location = str_replace(REPO_ROOT, '', $json_repo_path); // json path within repo
           require_once('../../../index/classes/IndexQueue.php');
           $queue = new IndexQueue('edited_items');
           $queue->enqueue($doc['id'], $data_location);
           chmod(INDEX_QUEUE_PATH . "/edited_items.db", 0777); // just incase we just created it.
   
       } // while file

   }// if dir
   
   header('Content-Type: text/javascript');
   header('Cache-Control: no-cache');
   header('Pragma: no-cache');
   echo json_encode($ajaxResponse);
   exit();


?>