<?php

  require_once('../config.php');
  require_once('../config_bgbase_dump.php');
  
  $page_size = 10000;
  // $page_size = 100; // debug
  
  $now = new DateTime();
  echo "Plants Start: " . $now->format(DATE_ATOM) . "\n";

  $offset = 0;
  $file_count = 0;
  while(true){
      
      echo "Starting at offset = $offset\n";
      
      $sql = "SELECT
            p.acc_num,
            p.acc_num_qual, 
            p.location_now,
            p.quadrant_now,
            l.location, 
            dwc.ScientificName,
            dwc.Family,
            dwc.Genus,
            dwc.SpecificEpithet,
            dwc.Country,
            dwc.CatalogNumber,
            dwc.Collector,
            dwc.County,
            dwc.FieldNotes,
            dwc.HigherGeography,
            dwc.Locality,
            dwc.remarks,
            dwc.StateProvince,
            dwc.EarliestDateCollected
        from
          plants as p 
        join darwin_core_living as dwc on p.ACC_NUM = dwc.CatalogNumber
  	  left join 
          locations as l
        on l.LOC_CODE = p.LOCATION_NOW
      order by
          acc_num ASC
      Limit $page_size
      OFFSET $offset";
      
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

      // break; // debug
  }
  
  $now = new DateTime();
  echo "Plants Finish: " . $now->format(DATE_ATOM) . "\n";
  
  function write_json_to_file($out, $file_count){
      
      $dir_path = REPO_ROOT . '/bgbase/plants';
      if(!file_exists($dir_path)) mkdir($dir_path, 0777, true);
      $file_path = $dir_path . '/' . str_pad($file_count, 3 ,"0",STR_PAD_LEFT)  . '.json';
      file_put_contents($file_path, JSON_encode($out));
      echo "\tWritten $file_path\n";
  }
  
  function doc_from_row($row){
      
      $doc = array();
      
      // required field
      $doc['item_type'] = 'Garden Plant';
  
      $doc['storage_location'] = 'Living Collections';
      
      $sl_path = '/Living Collections';
      
      if($row['location_now']){
          
          $doc['storage_location_bed_code_s'] = $row['location_now'];
          
          // garden is based on first letter of the bed code
          switch ( strtoupper(substr($row['location_now'],0,1)) ) {
            case 'X':
                $sl_path .= '/External';
                break;
            case 'Z':
                $sl_path .= '/Logan';
                $doc['storage_location_garden_s'] = "Logan Botanic Garden";
                break;
            case 'Y':
                $sl_path .= '/Benmore';
                $doc['storage_location_garden_s'] =  "Benmore Botanic Garden";
                break;
            case 'V':
                $sl_path .= '/Dawyck';
                $doc['storage_location_garden_s'] =  "Dawyck Botanic Garden";
                break;
            default:
                $sl_path .= '/Inverleith';
                $doc['storage_location_garden_s'] =  "Edinburgh Botanic Garden";
                break;
          }
          
          // add in the bed code
          $sl_path .= '/' . $row['location_now'];
          
          // keep track of the quadrant if need be
          if($row['quadrant_now']){
              $sl_path .= '/' . $row['quadrant_now'];
              $doc['storage_location_quadrant_s'] = $row['quadrant_now'];
          }
          
          
      }else{
          $sl_path .= '/Unplaced';
      }      
      
      $doc['storage_location_path'] = $sl_path;
      
      // if we have a description of the location add that
      if($row['location']){
          $doc['storage_location_description_s'] = $row['location'];
      }
      
      // id is the guid
      $doc['id'] = 'http://data.rbge.org.uk/living/' . $row['acc_num'] . $row['acc_num_qual'];
      $doc["link_out"] = 'http://data.rbge.org.uk/living/' . $row['acc_num'];
      
      $doc['derived_from'] = 'http://data.rbge.org.uk/living/' . $row['acc_num'];
   
      $doc['derivation_rank_i'] = 1;
      
      $doc['catalogue_number'] = $row['acc_num'] . $row['acc_num_qual'];
      
      // other catalogue numbers
      $doc['catalogue_number_other'] = array();
      $doc['catalogue_number_other'][] = $row['acc_num'];
      
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
      $title_parts[] = $row['acc_num'];
      $title_parts[] = $row['acc_num_qual'];
      $title_parts[] = $row['ScientificName'];
      $title_parts = array_filter($title_parts);
      $doc['title'] =  implode(' ', $title_parts); 
              
      return $doc;
      
  }



?>
