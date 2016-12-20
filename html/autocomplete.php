<?php

    // a generic autocomplete to look things up in the index
    
    require('../config.php');
    
    $term = @$_GET['term'];
    $term = trim($term);
    
    // the field we are looking into
    $field = @$_GET['field'];
    
    // treat case is semi sensible way (without creating dupicate fields)
    $case = @$_GET['case'];
    switch ($case) {
        case 'lower':
            $term = strtolower($term);
            break;
        case 'upper':
            $term = strtoupper($term);
            break;
        case 'title':
            $term = ucwords(strtolower($term));
            break;
        case 'species':
            $term = ucfirst(strtolower($term));
            break;
    }

    $term .= '*';
    $term = urlencode($term);
    
    $uri = REPO_SOLR_URI . "/query?facet.field=$field&facet=on&indent=on&q=$field:$term&rows=0&start=0&wt=json&facet.mincount=1";
    
    $ch = curl_init( $uri );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    
    // Send request.
    $result = curl_exec($ch);
    curl_close($ch);
/*
    echo '<pre>';
    print_r(json_decode($result));
    echo '</pre>';
    exit();
 */   
    
    $result = json_decode($result);

    $out = array();

    if($result->responseHeader->status == 0 && isset($result->response->docs) && isset($result->facet_counts->facet_fields->$field)){

        $values = $result->facet_counts->facet_fields->$field;

        for($i = 0; $i < count($values); $i = $i+2){
            $out[] = array('value' => $values[$i]);
        }

    }
    
    header('Content-Type: text/javascript');
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');
    echo json_encode($out);
    exit();
    
    
    
?>