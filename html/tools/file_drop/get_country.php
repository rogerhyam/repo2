<?php
    
    session_start();
    
    // the coordinates are past as lat,lon
    $coords = @$_GET['coords'];
    $out = array();
    
    // we check they aren't already in the session 
    if(!@$_SESSION['repo-tools-file-drop-country-coords']){
        $_SESSION['repo-tools-file-drop-country-coords'] = array();
    }
    
    if(!isset($_SESSION['repo-tools-file-drop-country-coords'][$coords]) && $coords){
        list($lat, $lon) = explode(',', $coords);
        $uri = "http://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$lon&zoom=0";

        $ch = curl_init( $uri );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_USERAGENT, 'RBGE Repo' );
        // Send request.
        $json = curl_exec($ch);
        
        curl_close($ch);
   
        $data = json_decode($json);
        $_SESSION['repo-tools-file-drop-country-coords'][$coords] = $data->address->country_code;
        $out['source'] = 'OSM Call';
    }else{
        $out['source'] = 'Session Cache';
    }
    
    $out['country_iso'] = strtoupper($_SESSION['repo-tools-file-drop-country-coords'][$coords]);

    header('Content-Type: text/javascript');
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');
    echo json_encode($out);
    exit();


?>