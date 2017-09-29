<?php

/*

    Assumes the following table is available in the image_archive db with write permission for Mobile
    
    DROP TABLE IF EXISTS `repo_item_images`;
    CREATE TABLE `repo_item_images` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `accession_barcode` varchar(14) NOT NULL,
      `accession_barcode_numeric` int(10) NOT NULL,
      `image_url` varchar(500) NOT NULL,
      `photographer` varchar(100) DEFAULT NULL,
      `repo_id` varchar(100) NOT NULL,
      `repo_last_indexed` datetime NOT NULL,
      `created` datetime NOT NULL,
      `modified` datetime NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `repo_id` (`repo_id`),
      KEY `accession_barcode` (`accession_barcode`),
      KEY `modified` (`modified`)
    ) AUTO_INCREMENT=1;
    

*/

require_once('../config.php');
require_once('../config_bgbase_dump.php');

// get the start date we are working from as the latest modified date in the existing database
$latest_stmt = $mysqli->prepare("SELECT max(repo_last_indexed) FROM `image_archive`.`repo_item_images`");
if(!$latest_stmt->execute()) error_log("Failed to find last indexed date. " . $latest_stmt->error);
$latest_stmt->bind_result($last_date);
$default_date = "2000-01-01T00:00:00Z"; 
if($latest_stmt->fetch()){
    if(!$last_date) $last_date = $default_date;
    $d = new DateTime($last_date);
    $last_date = $d->format('Y-m-d\TH:i:s.999\Z');// add 0.999 seconds on so we don't return the ones already in the db
}else{
  $last_date = $default_date;  
} 
$latest_stmt->reset();

echo "Starting update from $last_date\n" ;


// how many rows in each page
$repo_query =  REPO_SOLR_URI 
    . '/query?start=0&rows=100&sort=indexed_at+asc&q='
    . urlencode('indexed_at: ['. $last_date .' TO NOW] AND (item_type:"Specimen Photo" OR item_type:"Accession Photo" OR item_type:"Herbarium Specimen Scan") AND mime_type_s:"image/jpeg" ');

echo $repo_query;

// we need a mysqli prepared statement to use repeatedly.
$insert_stmt = $mysqli->prepare("INSERT INTO  `image_archive`.`repo_item_images` (accession_barcode, accession_barcode_numeric, image_url, photographer, repo_id, repo_last_indexed, created, modified) VALUES (?,?,?,?,?,?,NOW(),NOW()) ON DUPLICATE KEY UPDATE image_url = ?, photographer = ?, repo_last_indexed = ?, modified = NOW()");

if(!$insert_stmt){
    error_log($mysqli->error);
}


// loop till we get no results
while(true){
    
    // run the query
    $ch = curl_init($repo_query);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );        
    $result = json_decode(curl_exec($ch));
    curl_close($ch);

    // get out of here if we don't have any more results
    if(count($result->response->docs) < 1 ) break;

    // we do have results so loop through them
    foreach($result->response->docs as $doc){
        
        // get an identifier for it.
        if(!isset($doc->derived_from)) continue;
        $parts = explode('/', $doc->derived_from);
        $accession_barcode = array_pop($parts);
        
        // is it a barcode?
        if(preg_match('/^E[0-9]{8}$/', $accession_barcode)) $accession_barcode_numeric = substr($accession_barcode, 1);
        
        // if it is accession
        if(preg_match('/^[0-9]{8}/', $accession_barcode)) $accession_barcode_numeric = substr($accession_barcode, 0, 8);
        
        // image url - max size 4,000 pixels in one dir (will not enlarge beyond original size)
        $image_url = "http://repo.rbge.org.uk/image_server.php?kind=4000&path_base64="  . base64_encode($doc->storage_location_path);
        
        if(isset($doc->creator)){
            $creator = implode(';', $doc->creator);
        }else{
            $creator = '';
        }
        
        $d = new DateTime($doc->indexed_at);
        $repo_last_indexed = $d->format('Y-m-d H:i:s');
        
        $insert_stmt->bind_param('sisssssss',
            $accession_barcode,
            $accession_barcode_numeric,
            $image_url, // image url
            $creator, // photographer 
            $doc->id, // id in repo (will be uri) 
            $repo_last_indexed,
            $image_url,
            $creator,
            $repo_last_indexed
        );
    
        // Actually run insert
        if(!$insert_stmt->execute()){
            error_log("Failed to insert for $doc->id. " . $insert_stmt->error);
        }
        
        
     //   echo "\t$accession_barcode => $accession_barcode_numeric\n";
    
        // clear the statement for next time
        $insert_stmt->reset();
    
    }

    // set up the next query to run
    $rows = $result->responseHeader->params->rows;
    $start = $result->responseHeader->params->start;
    $new_start = $start + $rows;
    $repo_query = str_replace("?start=$start&","?start=$new_start&", $repo_query);
    echo "$rows from $start\n";
    
} // while true


// get the ones that have changed since last run

// work through pages







?>