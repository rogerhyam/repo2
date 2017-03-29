<?php
    require_once( '../../../config.php' );
    require_once( '../tools_config.php' );
    require_once( '../../inc/functions.php' );

    $genus = trim(@$_GET['genus']);
    $bed = trim(@$_GET['bed']);
    
    // sticky for form
    $_SESSION['repo-tools-photo-all-genus'] = $genus;
    $_SESSION['repo-tools-photo-all-bed'] = $bed;
    
    
    if(!$genus && !$bed){
       $_SESSION['repo-tools-photo-all-message'] = "You need to supply a genus name and/or bed to generate a list.";
       header('Location: index.php');
       exit;
    }else{
        unset($_SESSION['repo-tools-photo-all-message']);
    }


    // query the index

    // if we have a bed our query is just on plants
    // otherwise we need a complete list of accessions and plants
    if($bed){
        $query = 'item_type:"Garden Plant" AND storage_location_bed_code_s:"' . $bed . '"';
        
        if($genus){
            $query .= ' AND genus:"'. $genus .'"';
        }
        
    }else{
        $query = '(item_type:"Garden Plant" OR item_type:"Garden Accession") AND genus:"'. $genus .'"';
    }
    
    // run the query
    $result = query_solr($query);
    
    if($result->response->numFound == 0){
        $_SESSION['repo-tools-photo-all-message'] = "There are no plants and/or accessions matching the initial query. Please refine your search.";
        header('Location: index.php');
        exit;
    }
    
    // has the query exceeded expectations?
    if($result->response->numFound >= 1000){
        $_SESSION['repo-tools-photo-all-message'] = "There are over 1,000 plants and/or accessions matching the initial query. Please refine your search.";
        header('Location: index.php');
        exit;
    }
    
    // work through the list and see what is attached to it.
    $temp_file = tmpfile();
    // headers
    $headers[] = 'Accession&Qualifier';
    $headers[] = 'Family';
    $headers[] = 'Genus';
    $headers[] = 'Scientific Name';
    $headers[] = 'Bed Label';
    $headers[] = 'Bed Quad';
    $headers[] = '# Accession Photos';
    $headers[] = 'Link';
    fputcsv($temp_file, $headers);
    
    // the response
    foreach($result->response->docs as $doc){
        
        $row = array();
        
        $row[] = $doc->catalogue_number;
        
        if(isset($doc->family[0]))$row[] = $doc->family[0];
        else $row[] = '';
        
        if(isset($doc->genus[0]))$row[] = $doc->genus[0];
        else $row[] = '';
       
        if(isset($doc->scientific_name_plain[0]))$row[] = $doc->scientific_name_plain[0];
        else $row[] = '';
       
        if(isset($doc->storage_location_bed_code_s))$row[] = $doc->storage_location_bed_code_s;
        else $row[] = '';

        if(isset($doc->storage_location_quadrant_s))$row[] = $doc->storage_location_quadrant_s;
        else $row[] = '';
        
        // work out how many images there are
        $image_list = query_solr('item_type:"Accession Photo" AND derived_from:"'.$doc->id.'"');
        $row[] = $image_list->response->numFound;
      
        $row[] = $doc->id;

        fputcsv($temp_file, $row);
    }
    
    fseek($temp_file,0);
    header('Content-Description: File Transfer');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="photo_all_'.  date(DATE_ATOM)  .'.csv"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    while($out = fread($temp_file, 1024) ){
        echo $out;
    }
    
    exit;

?>