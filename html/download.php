<?php

    require_once('../config.php');
    require_once('inc/functions.php');
    
    // do we have a path
    $path = @$_GET['path'];
    
    if(!$path){
        header("HTTP/1.0 404 Not Found");
        echo "No path information supplied";
        exit;
    }
    
    $full_path = REPO_ROOT . $path;
    
    // does the file exist
    if(!file_exists($full_path)){
        header("HTTP/1.0 404 Not Found");
        echo "File does not exist";
        exit;
    }
    
    // load the record for the item
    $result = query_solr('storage_location_path:"'.$path.'"');
    
    if($result->responseHeader->status != 0 || $result->response->numFound !=1){
        header("HTTP/1.0 500 Internal Server Error");
        echo "Error retrieving metadata for file.";
        exit;
    }
    
    $doc = $result->response->docs[0];
    
    // is it embargoed?
    // similar logic in search_result_body.php
    if(isset($doc->embargo_date)){
        $embargo_date = new DateTime($doc->embargo_date);
        // fixme - allow them to do it within our network.
        if($embargo_date->getTimestamp() > time()){
            header("HTTP/1.0 403 Forbidden");
            echo "This file is embargoed until " . $embargo_date->format('Y-m-d');
            exit;
        }
    }

    // do we have a mime type
    if($doc->mime_type_s){
      header('Content-Type: ' . $doc->mime_type_s);
    }
    
    // force download
    $parts = pathinfo($path);
    header('Content-Disposition: attachment; filename="'. $parts['basename'] .'"');
    
    // finally stream it out with content disposition download
    readfile($full_path);
    
    
?>