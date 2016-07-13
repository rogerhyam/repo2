<?php

    require_once('../config.php');
    require_once('classes/Uploader.php');
    require_once('augmenter_switch.php');

    // starting off
    $now = new DateTime();
    echo "Indexer Started:". $now->format(DATE_ATOM) ."\n";

    $uploader = new Uploader(10000);
    
    // we are passed either a file or a directory to scan
    if(count($argv) < 2){
        echo "Usage: Please pass a json file(s) or directory(ies) path within REPO_ROOT containing json files to be indexed\n";
        exit(0);
    }
    
    // look at each file or directory
    for($i = 1; $i < count($argv); $i++){
        
        $path = REPO_ROOT . $argv[$i];
              
        if(is_dir($path)){
            directory_scan($path);
            continue;
        }
        
        if(is_file($path)){
            index_file($path);
            continue;
        }
        
    }
    
    $now = new DateTime();
    echo "Indexer Finished:". $now->format(DATE_ATOM) ."\n";
    
    
    function directory_scan($path){
        
        $dir_last_scan = dir_last_scanned($path);
        
        $files = scandir($path);
                        
        foreach($files as $file){
            
            if($file == '.') continue;
            if($file == '..') continue;
            
            // lets go down the way
            if(is_dir($path . "/" . $file)){
                directory_scan($path . "/" . $file);
            };
            
            // actually looking at a file
            if(substr($file, -5) == '.json'){
                
                $file_path = $path."/".$file;
                
                $mtime = filemtime($file_path);
                
                if($mtime > $dir_last_scan){
                    echo "INDEXING: $file_path\n";
                    index_file($file_path);
                }else{
                    echo "SKIPPING: $file_path not modified.\n";
                }
    
            }
        }
        
        // keep track of when we have scanned directories
        update_dir_scanned_list($path);
        
    }
    
    function dir_last_scanned($dir){
        if(file_exists('dirs_scanned.json')){
            $dirs = json_decode(file_get_contents('dirs_scanned.json'));
            if(array_key_exists($dir, $dirs)) return $dirs->$dir;
        }
        return 0;
    }
    
    function update_dir_scanned_list($dir){
        
        if(file_exists('dirs_scanned.json')){
            $dirs = json_decode(file_get_contents('dirs_scanned.json'));
        }else{
            $dirs = new stdClass();
        }
        
        $dirs->$dir = time();
        
        file_put_contents("dirs_scanned.json", json_encode($dirs));
        
    }
    
    function index_file($file_path){
        
        global $uploader;
        
        $docs = json_decode(file_get_contents($file_path));

        // exit;
        foreach($docs as $doc){

            // we have to have the location of the file containing the data
            $doc->data_location = $file_path;

            // we tag when it is being indexed
            $doc->indexed_at = 'NOW';
            
            // give the augmenters a chance to add to the document
            augment($doc);
           // var_dump($doc);
           
           
           
           // lastly we remove any fields containing stuff that shouldn't be sent to SOLR
           $vars = get_object_vars($doc);
           foreach($vars as $k => $v){
             if(preg_match('/^IGNORE_/', $k)) unset($doc->$k);  
           } 
           
           $uploader->add_document($doc);
        }

        $uploader->submit_now();
        
    }
    
    
    // load a document from outside
 
    // $file_path = '/bgbase/herbarium_specimens/000.json';

    // $file_path = '/test/reference_doc.json';


    

?>