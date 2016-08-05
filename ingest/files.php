<?php

    require_once('../config.php');
    
    // used to ingest generic "files" that have been placed in the archive.
    // basically scans the directory structure looking for files (or directories) that don't have *.json metadata files
    // creates basic *.json files that have derivation links back up the director structure.
    
    // establish the kicking off point. It must have a json file describing it - everything new below it will be derived from it
    
    if(count($argv) < 2){
        echo "Please pass the directory path within the repository that needs to be ingested.\n";
        echo "You can add 'true' if you want to overwrite existing .json files";
        exit(0);
    }
    
    $path = REPO_ROOT . $argv[1];
    
    if(isset($argv[2]) && strtolower($argv[2]) == 'true'){
        $overwrite = true;
        echo "\tOverwrite set to TRUE - will replace existing .json metadata files";
    }else{
        $overwrite = false;
        echo "\tOverwrite set to FALSE - will skip existing .json metadata files";
    }

    // no slash ending
    $path = rtrim($path, "/");
    
    if(!file_exists($path)){
        echo "Starting directory '$path' does not exist!\n";
        exit;
    }

    if(!is_dir($path)){
        echo "Starting path '$path' is not a directory!\n";
        exit;
    }
    
    // starting dir must have a json description act as a template
    $parent_json_path = $path . ".json";
    
    if(!file_exists($parent_json_path)){
        echo "Starting directory lacks a json file '$parent_json_path'!\n";
        exit;
    }
    
    echo $parent_json_path . "\n";
    $template = json_decode(file_get_contents($parent_json_path));
    
    // some things need to be set up in the file we got the template from
    // the regular template is unlikely to be complete
    $tdoc = $template[0];
    $tdoc->id = get_identifier_for_repo_file($argv[1]);
    $tdoc->item_type = 'Directory';
    $tdoc->data_location = str_replace(REPO_ROOT, '', $parent_json_path);
    $tdoc->storage_location = "Repository";
    $tdoc->storage_location_path = str_replace(REPO_ROOT, '',$path);
    
    $out = array();
    $out[] = $tdoc;
    file_put_contents($parent_json_path, JSON_encode($out, JSON_PRETTY_PRINT));
    echo "\tWritten $parent_json_path\n";
    
    directory_scan($path, $template, $overwrite);
    
    function directory_scan($path, $template, $overwrite){

        // we use a file info thing        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        
        echo "Processing dir: $path\n";

        $files = scandir($path);

        foreach($files as $file){

            // ignore files starting with a dot
            if(preg_match('/^\./', $file)) continue;
            
            // ignore existing json files
            if(substr($file, -5) == '.json'){
                echo "\tIgnoring $file\n";
                continue;
            }
            
            // ignore existing .json.jpg files - these are thumbnails
            if(substr($file, -9) == '.json.jpg'){
                echo "\tIgnoring $file\n";
                continue;
            }
            
            
            // full path to a data file
            $file_path = $path . "/" . $file;
            
            // path to the accompanying json file
            $parts = pathinfo($file_path);
            $json_path = $path . "/" . $parts['filename'] . '.json';
            
            // if the json already exist then skip it
            if(file_exists($json_path)){
                if(!$overwrite){
                    echo "\tIgnoring $file because json metadata file already exists\n";
                    continue;
                }else{
                    echo "\t$json_path exists - overwriting\n";
                }
            }

            // OK let's create a json file to keep it company.
            
            $doc = clone $template[0];
            
            // we just set the title, mimetype and other bits relevant to the 
            // files we understand
            $file_title = str_replace('.', ' ', str_replace('_', ' ', $file));
            if(isset($doc->title)){
                $doc->title = $doc->title . $file_title;
            }else{
                $doc->title = $file_title;
            }
            
            // id - all important
            $file_repo_path = str_replace(REPO_ROOT, '', $file_path);
            $doc->id =  get_identifier_for_repo_file($file_repo_path);
            
            if(is_dir($file_path)){
                $doc->item_type = 'Directory';
            }else{
                $doc->item_type = 'Document';
            }
            
            // all files are derived from their directory
            $dir_repo_path = str_replace(REPO_ROOT, '', $path);
            $doc->derived_from = get_identifier_for_repo_file($dir_repo_path);
            
            // by definition it is always stored in the repository
            $doc->storage_location = "Repository";
            $doc->storage_location_path = $file_repo_path;
            
            // mime type
            $mimetype = finfo_file($finfo, $file_path);
            if($mimetype) $doc->mime_type_s = $mimetype;
            
            // this is where the metadata is - may be reset by indexer
            $json_repo_path = str_replace(REPO_ROOT, '', $json_path);
            $doc->data_location = $json_repo_path;
            
            // create a thumbnail if we can
            $summary_image_path = $json_path . '.jpg';
            if($mimetype == 'application/pdf'){
                $im = new Imagick();
                $im->setResolution(300,300);
                $im->readimage($file_path . '[0]'); 
                $im->setImageOpacity(1);
                $im->resizeImage(1000,1000,Imagick::FILTER_LANCZOS,1, true);
                $im->setImageFormat('jpeg');
                $im->writeImage($summary_image_path); 
                $im->clear(); 
                $im->destroy();
            }
            
            // if we were successful at creating it add it to the doc
            if(file_exists($summary_image_path)){
                $doc->summary_image_s = str_replace(REPO_ROOT, '', $summary_image_path);
            }
            
            // write it out
            $out = array();
            $out[] = $doc;
            file_put_contents($json_path, JSON_encode($out, JSON_PRETTY_PRINT));
            echo "\tWritten $json_path\n";
            
            // if this is a directory then go down it
            if(is_dir($path . "/" . $file)){
                directory_scan($file_path, $template, $overwrite);
            };
            
            
            
        }

        
        
    }
    

    


?>
