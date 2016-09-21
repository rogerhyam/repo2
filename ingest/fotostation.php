<?php

    require_once('../config.php');
    
    
    /*
    
        // use this in the crontab to keep the directories in sync between the two systems
        rsync -a "/media/fotostation/Images Done/Archive" "/media/repo/fotostation"
    
    */
    
    // load the blacklist
    $blacklist = file('fotostation_blacklist.txt');
    $blacklist = array_map('trim', $blacklist);
    
      
    // scan all the directories looking for missing or old .json file
    process_dir(REPO_ROOT . '/fotostation/Archive/');

    function process_dir($dir_path){
                
        global $blacklist;
        
        echo "DIR: $dir_path\n";
        
        $files = scandir($dir_path);
        
        foreach($files as $file){
            
            if($file == '.') continue;
            if($file == '..') continue;
            
            // check file isn't blacklisted as already imported
            if(in_array($file, $blacklist)){
                echo "\tBlacklisted - continuing\n";
                continue;
            }
            
            $jpg_path = $dir_path . $file;
            
            if(is_dir($jpg_path)){
                process_dir($jpg_path . '/');
            }else{
                
                echo "FILE: $jpg_path\n";
    
                // only the jpegs
                if(!preg_match('/\.jpg$/',$file, $matches)) continue;

                // calc name of json file associated with the jpg
                $parts = pathinfo($jpg_path);
                $json_path = $dir_path . $parts['filename'] . '.json';
                
                // if the json file already exists and is newer than jpg continue
                if(file_exists($json_path) && filemtime($json_path) > filemtime($jpg_path)){
                    echo "\tjson newer than jpg ignoring\n";
                    continue;  
                } 
                
                // building an index object
                $doc = array();
                
                // id for things where the hierarchy is unlikely to have meaning
                $image_repo_location = str_replace(REPO_ROOT, '', $jpg_path);
                
                // vital fields
                $doc['id'] = 'http://repo.rbge.org.uk/id/' . base64_encode($image_repo_location);
                $doc["storage_location"] = "Repository";
                $doc["storage_location_path"] = $image_repo_location;
                $doc['title'] = basename($jpg_path);
                $doc['item_type'] = "Image";
                $doc['mime_type_s'] = 'image/jpeg';
                               
                // build the exif data
                echo $jpg_path;
                $exif = exif_read_data($jpg_path);
                $exif_string = "";
                array_walk_recursive($exif, function($value, $key) use (&$exif_string){
                      if(strlen($exif_string) > 0)$exif_string .= " ";
                      $exif_string .= $value;
                });
                //echo $exif_string . "\n";
                $doc['exif_json_s'] = json_encode($exif);
                $doc['exif_txt'] = $exif_string;
                
                // build the iptc data
                $info = array();
                $size = getimagesize($jpg_path, $info);
                if(isset($info['APP13'])){
                    
                    $iptc =  iptcparse($info['APP13']);
                    unset($iptc['2#000']); // blob data
                    unset($iptc['2#040']); // blob data
                    $iptc_string = "";
                    array_walk_recursive($iptc, function($value, $key) use (&$iptc_string){
                          if(strlen($iptc_string) > 0)$iptc_string .= " ";
                          $iptc_string .= $value;
                    });
                    $doc['iptc_json_s'] = str_replace('\\u0000', '', json_encode($iptc_string));
                    $doc['iptc_txt'] = $iptc_string;
                    
                    //print_r($iptc);
                    
                    // fill in some useful fields from the IPTC data
                    $keywords = array();
                    $caption_editor = '';
                    foreach($iptc as $tag => $values){
                        foreach($values as $val){
                            
                            switch ($tag) {
                                
                                // keywords
                                case '2#025':
                                    $doc['keywords_ss'][] = $val;
                                    $keywords[] = $val;
                                    break;
                                
                                // content    
                                case '2#120':
                                    $doc['content'] = $val;
                                    break;
                                    
                                // copyright holder
                                case '2#116':
                                    $doc['copyright_holder'] = $val;
                                    break;
                                
                                // source it was digitised from
                                case '2#115':
                                    $doc['source_s'] = $val;
                                    break;
                                
                                // Writer-Editor    
                                case '2#122':
                                    $caption_editor = " Caption by: $val";
                                    break;
                                    
                                // Genus
                                case '2#105':
                                    $genus = trim($val);
                                    if(str_word_count($genus) != 1) break;
                                    $doc['genus'] = ucfirst(strtolower($genus));
                                    break;
                                
                                // epithet
                                case '2#110':
                                    $epithet = trim($val);
                                    if(str_word_count($epithet) != 1) break;
                                    $doc['epithet'] = strtolower($epithet);
                                    break;
                                    
                                // Family
                                case '2#005':
                                    $family = trim($val);
                                    if(str_word_count($family) != 1) break;
                                    $doc['family'] = ucfirst(strtolower($family));
                                    break;
                                
                                // accession number?
                                case '2#080':
                                    $cat = trim($val);
                                    $cat = str_replace('.', '', $cat); // sometimes have dot between year and rest of accession number
                                    $cat = str_replace(' ', '', $cat); // sometimes have space between acc num and qualifier
                                    $cat = str_replace('*', '', $cat); // just incase they do the * way of citing it
                                    
                                    // do we end up with an accession number
                                    if(preg_match('/^[0-9]{8}/', $cat)){
                                        $doc['derived_from'] = 'http://data.rbge.org.uk/living/' . $cat;
                                        $doc['item_type'] = 'Accession Photo';
                                    }
                                    
                                    // do we end up with a barcode?
                                    if(preg_match('/^E[0-9]{8}$/', $cat)){
                                        $doc['derivide_from'] = 'http://data.rbge.org.uk/herb/' . $cat;
                                        $doc['item_type'] = 'Specimen Photo';
                                    }
                                    
                                    break;
                                    
                                // created date?
                                case '2#055':
                                    $date = DateTime::createFromFormat('Ymd', trim($val));
                                    if(!$date) break;
                                    $doc['object_created'] = $date->format('Y-m-d\TH:i:s\Z');
                                    $doc['object_created_year'] = $date->format('Y');
                            }
                            
                        }
                        
                    }

                    // add the keywords onto the end of the content
                    $doc['content'] = $doc['content'] . $caption_editor . ' Keywords: ' . implode('; ', $keywords);
                    
                    // make the title more meaningful
                    if(strlen($doc['content']) > 40){
                       $doc['title'] .= ' ' . substr($doc['content'], 0, strpos(wordwrap($doc['content'], 40), "\n")) . ' ... '; 
                    }else{
                        $doc['title'] .= ' ' . $doc['content'];
                    }
                    
                }
                
                // write the json file out
                $out = array();
                $out[] = $doc;
                file_put_contents($json_path, JSON_encode($out, JSON_PRETTY_PRINT));
                echo "\tWritten $json_path\n";

            }
            
            
        }
        
        
        
    }
    


?>