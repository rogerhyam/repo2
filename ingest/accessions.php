<?php
    
    require_once('../config.php');
    require_once('../config_bgbase_dump.php');
    
    $page_size = 100000;
    //$page_size = 10; // debug
    
    $now = new DateTime();
    echo "Accessions Start: " . $now->format(DATE_ATOM) . "\n";
  
    $offset = 0;
    $file_count = 0;
    while(true){
        
        echo "Starting at offset = $offset\n";
        
        $sql = "SELECT * FROM darwin_core_living ORDER BY GloballyUniqueIdentifier LIMIT $page_size OFFSET " . $offset;
        $response = $mysqli->query($sql);
        echo "\tGot {$response->num_rows}\n";
        
        // give up if we have done them all
        if($response->num_rows == 0) break;
        
        // write out these to files
        $out = array();
        while($row = $response->fetch_assoc()){
            $out[] = doc_from_row($row);
        }
        write_json_to_file($out, $file_count);
        $file_count++;
        $offset = $offset + $page_size;

        //break; // debug
    }
    
    $now = new DateTime();
    echo "Accessions Finish: " . $now->format(DATE_ATOM) . "\n";
    
    
    function write_json_to_file($out, $file_count){
        $file_path = REPO_ROOT . '/bgbase/accessions/' . str_pad($file_count, 3 ,"0",STR_PAD_LEFT)  . '.json';
        file_put_contents($file_path, JSON_encode($out));
        echo "\tWritten $file_path\n";
    }
    
    function doc_from_row($row){
        
        $doc = array();
        
        // required field
        $doc['item_type'] = 'Garden Accession';
    
        $doc['storage_location'] = 'Living Collections Catalogue';
        $doc['storage_location_path'] = '/Living Collections Catalogue';
        
        // id is the guid
        $doc['id'] = $row['GloballyUniqueIdentifier'];
        $doc["link_out"] = $row['GloballyUniqueIdentifier'];
        
        $doc['catalogue_number'] = $row['CatalogNumber'];
        
        // other catalogue numbers
        $doc['catalogue_number_other'] = array();
        if(isset($row['CatalogNumberNumeric'])) $doc['catalogue_number_other'][] = $row['CatalogNumberNumeric'];
        if(isset($row['CollectorNumber'])) $doc['catalogue_number_other'][] = $row['CollectorNumber'];
        if(isset($row['OtherCatalogNumbers'])) $doc['catalogue_number_other'][] = $row['OtherCatalogNumbers'];
        
        // scientific name - always have?
        $doc['scientific_name_html'] = $row['ScientificName'];
        $doc['scientific_name_plain'] = strip_tags($row['ScientificName']);
        
        // taxonomy
        if($row['Family']) $doc['family'] = $row['Family'];
        if($row['Genus']) $doc['genus'] = $row['Genus'];
        if($row['SpecificEpithet']) $doc['epithet'] = $row['SpecificEpithet'];
        
        // geography
        $location_parts = array();
        $location_parts[] = $row['HigherGeography'];
        $location_parts[] = $row['StateProvince'];
        $location_parts[] = $row['County'];
        $location_parts[] = $row['Locality'];
        $location_parts = array_filter($location_parts);
        $doc['location'] = implode(':', $location_parts);
        
        if($row['MaximumElevationInMeters']){
            $el = trim(str_replace('M', '',$row['MaximumElevationInMeters']));
            if(is_numeric($el)){
                $doc['elevation'] = (float)$el;
            }
        } 
        
        if( $row['DecimalLatitude'] 
            && $row['DecimalLongitude']
            && $row['DecimalLatitude'] <= 90 
            && $row['DecimalLatitude'] >= -90
            && $row['DecimalLongitude'] <= 180
            && $row['DecimalLongitude'] >= -180
            ){
            $doc['geolocation'] = $row['DecimalLatitude'] . ',' . $row['DecimalLongitude'];
        }
        
        if($row['Collector']) $doc['creator'] = $row['Collector'];
        
        if($row['FieldNotes']) $doc['content'] = $row['FieldNotes'];
        if($row['Country']) $doc['country_iso'] = $row['Country'];
        
        // dates
        if($row['EarliestDateCollected']){
            try{
                $date = new DateTime($row['EarliestDateCollected']);
                $doc['object_created'] = $date->format('Y-m-d\TH:i:s\Z');
                $doc['object_created_year'] = $date->format('Y');
            }catch(Exception $e){
                echo "\tProblem parsing date {$row['EarliestDateCollected']} for {$row['CatalogNumber']}\n";
            }
        }
        
        // this could be nicer
        $title_parts = array();
        $title_parts[] = $row['Collector'];
        $title_parts[] = $row['CollectorNumber'];
        $title_parts[] = $row['ScientificName'];
        $title_parts = array_filter($title_parts);
        $doc['title'] =  implode(' ', $title_parts); 
                
        return $doc;
        
    }
    
?>