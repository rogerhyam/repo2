<?php

    require_once('../config.php');
    require_once('classes/Uploader.php');
    require_once('classes/IndexQueue.php');
    require_once('augmenter_switch.php');

    // starting off
    $now = new DateTime();
    echo "Indexer Started:". $now->format(DATE_ATOM) ."\n";

    $uploader = new Uploader(10000);
    
    // we are passed either a file or a directory to scan
    if(count($argv) < 2){
        echo "Usage: Please pass a json file(s) or directory(ies) path within REPO_ROOT containing json files to be indexed\n";
        echo "\tOptionally follow it with a \n";
        exit(0);
    }
    
    // have they set a since date?
    if(count($argv) == 3){
        $since = $argv[2];
    }else{
        $since = 0;
    }
    
    // look at each file or directory
    for($i = 1; $i < count($argv); $i++){
        
        // first look to see if it is a queue database
        $path_parts = pathinfo($argv[$i]);
        if(isset($path_parts['extension']) && $path_parts['extension'] == 'db'){
            queue_scan($path_parts['filename']);
            continue;
        }
        
        // if it isn't a queue it will be relative
        // to the data directory root
        $path = REPO_ROOT . $argv[$i];
    
        if(!file_exists($path)){
            echo "$path does not exist!\n";
        }
    
        if(is_dir($path)){
            directory_scan($path, $since);
            continue;
        }
        
        if(is_file($path)){
            index_file($path);
            continue;
        }
        
    }
    
    $now = new DateTime();
    echo "Indexer Finished:". $now->format(DATE_ATOM) ."\n";
    
    
    function queue_scan($queue_name){
        
        echo "Processing queue: $queue_name\n";
        
        $queue = new IndexQueue($queue_name);
        
        while($relative_path = $queue->get_priority_file()){
            $full_path = REPO_ROOT . $relative_path;
            index_file($full_path, $queue);
        }
        
    }
    
    function directory_scan($path, $since){
        
        echo "Processing dir: $path\n";
                
        $files = scandir($path);
                        
        foreach($files as $file){
            
            // ignore files starting with a dot
            if(preg_match('/^\./', $file)) continue;
            
            // lets go down the way
            if(is_dir($path . "/" . $file)){
                directory_scan($path . "/" . $file, $since);
            };
            
            // actually looking at a file
            if(substr($file, -5) == '.json'){
                
                $file_path = $path."/".$file;
                
                $mtime = filemtime($file_path);
                
                if($mtime > $since){
                    echo "INDEXING: $file_path\n";
                    index_file($file_path);
                }else{
                    echo "SKIPPING: $file_path not modified since $since.\n";
                }
    
            }
        }
        
    }
    
    
    function index_file($file_path, $queue = null){
        
        global $uploader;
        
        echo "Processing file: $file_path\n";
        
        // get the contents of the file
        $json = @file_get_contents($file_path);
        if(!$json){
            echo "ERROR can't access $file_path\n";
            if($queue)$queue->shelve($file_path);
            return;
        }
        
        $docs = json_decode($json);
        if(!$docs){
            echo "ERROR can't parse JSON in $file_path\n";
            if($queue)$queue->shelve($file_path);
            return;
        }
        
        foreach($docs as $doc){

            // we have to have the location of the file containing the data
            // but this should be within the scope of the repository - the full path might change
            $relative_file_path = str_replace(REPO_ROOT, '', $file_path);
            $doc->data_location = $relative_file_path;

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

           // we pop it off the queue (if we are doing this as part of the queue)
           // slight danger here in that if the upload fails we have removed it from the index queue
           if($queue){
               $queue->dequeue($doc->id);
           }

        }

        $uploader->submit_now();
        
        
    }
    
    
    // load a document from outside
 
    // $file_path = '/bgbase/herbarium_specimens/000.json';

    // $file_path = '/test/reference_doc.json';



?>