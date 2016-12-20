<?php

  require_once('../config.php');
  require_once('../config_bgbase_dump.php');
  
  $page_size = 10000;
  // $page_size = 1000; // debug
  
  $now = new DateTime();
  echo "Collector Books Start: " . $now->format(DATE_ATOM) . "\n";

  $offset = 0;
  $file_count = 0;
  while(true){
      
      echo "Starting at offset = $offset\n";
      
      $sql = "SELECT
      coll_id,
      coll_num,
      coll_name,
      country_code,
      CONCAT_WS('; ', sub_cnt1, sub_cnt2, sub_cnt3, locality) as location,
      round(concat( if(long_dir = 'W', '-', ''), long_degree + if( long_minute is not null, (long_minute + if(long_second is not null, long_second/60, 0)) /60, 0) ), 6) as decimal_longitude,
      round(concat( if(lat_dir = 'S', '-', ''), lat_degree + if( lat_minute is not null, (lat_minute + if(lat_second is not null, lat_second/60, 0)) /60, 0) ), 6) as decimal_latitude,
      convert(
        case coll_dt_qual
                when 'Y' then year(DATE_ADD('1967-12-31', INTERVAL coll_dt DAY))
                when 'M' then concat(monthname(DATE_ADD('1967-12-31', INTERVAL coll_dt DAY)), ' ', year(DATE_ADD('1967-12-31', INTERVAL coll_dt DAY)))
                else DATE_ADD('1967-12-31', INTERVAL coll_dt DAY)
        end,
      char) as date_collected,
      cb.habitat,
      cb.name_num,
      cb.NAME_FREE as name_free,
      cb.GENUS as genus_free,
      cb.family as family_free,
      n.FAMILY as family,
      n.GENUS as genus,
      n.SPECIES as epithet,
      n.NAME_FULL as name_plain,
      n.NAME_HTML as name_html
      from coll_books as cb
      left join `names` as n on cb.NAME_NUM = n.NAME_NUM
      order by
           cb.coll_id ASC, cb.coll_num ASC
      Limit $page_size
      OFFSET $offset";
      
      $response = $mysqli->query($sql);
      echo "\tGot {$response->num_rows}\n";
      
      // give up if we have done them all
      if($response->num_rows == 0) break;
      
      // write out these to files
      $out = array();
      while($row = $response->fetch_assoc()){
          if(!$row['coll_id']) continue;
          $out[] = doc_from_row($row);
      }
      write_json_to_file($out, $file_count);
      $file_count++;
      $offset = $offset + $page_size;

      // break; // debug
  }
  
  $now = new DateTime();
  echo "Collector Books Finish: " . $now->format(DATE_ATOM) . "\n";
  
  function write_json_to_file($out, $file_count){
      
      $dir_path = REPO_ROOT . '/bgbase/coll_books';
      if(!file_exists($dir_path)) mkdir($dir_path, 0777, true);
      $file_path = $dir_path . '/' . str_pad($file_count, 3 ,"0",STR_PAD_LEFT)  . '.json';
      file_put_contents($file_path, JSON_encode($out, JSON_PRETTY_PRINT));
      echo "\tWritten $file_path\n";
  }
  
  function doc_from_row($row){
      
      $doc = array();
      
      // required field
      $doc['item_type'] = 'Collector Book Entry';
  
      $doc['storage_location'] = 'BGBASE';
      
      $doc['storage_location_path'] = '/BGBASE';
      
      // id is the guid
      $doc['id'] = 'http://data.rbge.org.uk/coll_books/' . base64_encode($row['coll_id'] . ' ~ ' . $row['coll_num']);
      $doc['collector_id_s'] = $row['coll_id'];
      $doc['collector_number_s'] = $row['coll_num'];
      
      $doc['derivation_rank_i'] = 1;
      
      $doc['catalogue_number'][] = $row['coll_id'] . ' ~ ' . $row['coll_num'];
      
      // scientific name - always have?
      if($row['name_num']){
          
        // has a link to the names table
        if($row['family']) $doc['family'] = ucfirst(strtolower($row['family']));
        if($row['genus']) $doc['genus'] = ucfirst(strtolower($row['genus']));
        if($row['epithet']) $doc['epithet'] = strtolower($row['epithet']);
        $doc['scientific_name_html'] = $row['name_html'];
        $doc['scientific_name_plain'] = strip_tags($row['name_plain']);
          
      }else{
          
          // no formal name
          if($row['name_free']){
              $doc['scientific_name_html'] = $row['name_free'];
              $doc['scientific_name_plain'] = $row['name_free'];
          }
                   
          if($row['family_free']) $doc['family'] = ucfirst(strtolower($row['family_free']));
          if($row['genus_free']) $doc['genus'] = ucfirst(strtolower($row['genus_free']));
          
      }
      
      
      // geography
      if($row['location']){
          $doc['location'] = $row['location'];
      }
      if($row['country_code']) $doc['country_iso'] = $row['country_code'];      
      
      if( $row['decimal_latitude'] 
          && $row['decimal_longitude']
          && $row['decimal_latitude'] <= 90 
          && $row['decimal_latitude'] >= -90
          && $row['decimal_longitude'] <= 180
          && $row['decimal_longitude'] >= -180
          ){
          $doc['geolocation'] = $row['decimal_latitude'] . ',' . $row['decimal_longitude'];
      }
      
      if($row['coll_name']) $doc['creator'] = $row['coll_name'];
      
      if($row['habitat']) $doc['content'] = $row['habitat'];

      
      // dates
      if($row['date_collected']){
          try{
              $date = new DateTime($row['date_collected']);
              $doc['object_created'] = $date->format('Y-m-d\TH:i:s\Z');
              $doc['object_created_year'] = $date->format('Y');
          }catch(Exception $e){
              echo "\tProblem parsing date {$row['date_collected']} for {$row['coll_id']} ~ {$row['coll_num']}\n";
          }
      }
      
      // Build a title
      $title_parts = array();
      $title_parts[] = $row['coll_name'];
      $title_parts[] = '[' . $row['coll_id'] . ']';
      $title_parts[] = $row['coll_num'];
      $title_parts[] = @$doc['scientific_name_plain'];
      $title_parts = array_filter($title_parts);
      $doc['title'] =  implode(' ', $title_parts); 
              
      return $doc;
      
  }



?>
