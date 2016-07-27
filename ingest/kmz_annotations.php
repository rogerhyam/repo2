<?php

     require_once('../config.php');

     $now = new DateTime();
     echo "Locations Start: " . $now->format(DATE_ATOM) . "\n";

    // scan across the locations data dir
    $path = REPO_ROOT . '/geospatial/kmz';
    directory_scan($path);

    $now = new DateTime();
    echo "\nLocations Finish: " . $now->format(DATE_ATOM) . "\n";
    

    
    // find kmz files
    
    // check for out of date or missing .json files
    
    // generate new .json files if needed.
    
    
    function ingest_file($kmz_path, $json_path){
        
        echo "Ingesting $kmz_path\n";
        
        // unpack the kml from the kmz package
        $za = new ZipArchive();
        $za->open($kmz_path);
        $temp_dir = sys_get_temp_dir() . uniqid('/');
        mkdir($temp_dir, 0777, true);
        $za->extractTo($temp_dir, 'doc.kml');
        $kml_path = "$temp_dir/doc.kml";
              
        // load the kml into a dom object
        $xmlDoc = new DOMDocument();
        $xmlDoc->load($kml_path);
        $xpath = new DOMXpath($xmlDoc);
        $xpath->registerNamespace('kml', 'http://www.opengis.net/kml/2.2');
        
        // use xpath to pull out the list
        $placemarks = $xpath->query("//kml:Placemark");
        
        $out = array();
        $placemark_count = -1;
        foreach($placemarks as $placemark){
            
            $placemark_count++;
            
            foreach($placemark->childNodes as $child){
                if($child->nodeName == 'name'){
                    
                    $name = $child->textContent;
                    
                    $doc = array();
                    
                    // we are only interested in placemarks that match things in the repo
                    if(preg_match('/^[0-9]{8}/', $name)){
                        // sometime the accessions have a * separating the qualifier
                        $name = str_replace("*", '', $name);
                        $name = strtoupper($name);
                        $doc['annotation_of_s'] = 'http://data.rbge.org.uk/living/' . $name;
                    }else if(preg_match('/^E[0-9]{8}$/', $name)){
                        // matches herbarium specimen
                        $doc['annotation_of_s'] = 'http://data.rbge.org.uk/herb/' . $name;
                    }else{
                        // doesn't match anything
                        continue 2;
                    }
                    
                    // this annotation will update the target
                    $doc['annotation_updates_b'] = true;
            
                     // find the point
                    $points = $placemark->getElementsByTagNameNS('http://www.opengis.net/kml/2.2', 'Point');
                    if(!$points->length) continue 2; // not a point placemark
                    $point = $points->item(0); // just take the first - there should only be one i think

                    // find the coordinates
                    $coords = $point->getElementsByTagNameNS('http://www.opengis.net/kml/2.2', 'coordinates');
                    if(!$coords->length) continue 2; // no location
                    $coord = $coords->item(0);

                    // split it to three - notice order
                    list($lon, $lat, $alt) = explode(',', $coord->textContent);

                    $doc['geolocation'] = trim($lat) . ',' . trim($lon);

                    // do something sensible with altitude
                    // if it is clamptoground or clamptoseafloor or absolute we can assume the number is height above sea level
                    // alternatives are relativetoground or relativetoseafloor - where we would have to know the local terrain height - more or a mapping feature.
                    $modes = $point->getElementsByTagNameNS('http://www.opengis.net/kml/2.2', 'altitudeMode');
                    if($modes->length){
                        $mode = $modes->item(0)->textContent;
                        if($alt && $mode && ($mode == 'clampToGround' || $mode == 'clampToSeaFloor' || $mode == 'relative')){
                            $doc['elevation'] = $alt;
                        }
                    }

                    // ID need to identify this node within the document
                    if($placemark->attributes->getNamedItem('id')){
                        $qualifier = $placemark->attributes->getNamedItem('id')->nodeValue;
                    }else{
                        $qualifier = $placemark_count;
                    }
                    
                    $doc['id'] = get_identifier_for_repo_file(str_replace(REPO_ROOT, '', $kmz_path), $qualifier);
                    $doc['item_type'] = 'Annotation';
                    $doc["title"] =  "Geolocation Annotation from KMZ file " . pathinfo($kmz_path)['basename'] . ". Placemark $qualifier";
                    $doc["storage_location_path"] = str_replace(REPO_ROOT, '', $kmz_path);
                                        
                    // add it to the output array
                    $out[] = $doc;

                } // has name element
                
            } // element in placemark

        } // placemarks
        
        $annotation_count = count($out);
        echo "\tFound $placemark_count Placemarks and created $annotation_count annotations;\n";
        
        // write $out to json file
        file_put_contents($json_path, JSON_encode($out));
        echo "\tWritten $json_path\n";
        
        
    } // ingest file

    function is_accession($nodes){
        $id = $nodes[0]->textContent;
        if(preg_match('/^[0-9]+/', $id[0])){
            return true;
        }else{
            return false;
        }
    }
    
    function directory_scan($path){
        
        echo "\tProcessing dir: $path\n";
        
        $files = scandir($path);
                        
        foreach($files as $file){

            // ignore files starting with a dot
            if(preg_match('/^\./', $file)) continue;

            // lets go down the way
            if(is_dir($path . "/" . $file)){
                directory_scan($path . "/" . $file);
                continue;
            };

            // ignore non kmz files
            if(!preg_match('/\.kmz$/', $file)) continue;
            
            $file_path = $path."/".$file;
            
            echo "\tFound: $file_path \n";
            
            // is there a json companion file?
            $json_file_path = preg_replace('/\.kmz$/', '.json', $file_path);
            
            // no companion file?
            if(!file_exists($json_file_path)){
                echo "\tJSON file missing: $json_file_path\n";
                ingest_file($file_path, $json_file_path);
                continue;
            }
            
            // companion file lacking            
            $file_time = filemtime($file_path);
            $json_time = $mtime = filemtime($json_file_path);
            if($json_time < $file_time){
                echo "\tJSON is older than data file\n";
                ingest_file($file_path, $json_file_path);
            }else{
                echo "\tJSON is up to date\n";
            }

        }
        
    }
    


?>