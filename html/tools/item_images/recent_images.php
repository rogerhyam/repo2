<?php

    require_once( '../../../config.php' );
    
    $user = @$_SESSION['repo-tools-username'];

    // get a list of recently added images from their history
    if(file_exists("images/$user/history.txt")){
        $image_paths = file("images/$user/history.txt");
    }else{
        $image_paths = array();
    }
    
    $new_history = array();
    
    foreach($image_paths as $image_path){
        
        // dodge blanks
        if(strlen(trim($image_path)) < 1) continue;
        
        // check if it has been indexed. If so remove it from the list if it is in the index
        if(is_in_index(trim($image_path))) continue;
        
        echo "<img class=\"repo-recent-image\" src=\"/image_server.php?kind=200&path=$image_path\" />";
        
        $new_history[] = $image_path;
        
    }
    
    // write the altered array back out
    if(!file_exists("images/$user")) mkdir("images/$user");
    file_put_contents( "images/$user/history.txt", $new_history );


function is_in_index($storage_location_path){
    
    $query = "storage_location_path:\"$storage_location_path\"";
    $uri = REPO_SOLR_URI . '/query?q=' . urlencode($query) . '&rows=20';
    $ch = curl_init( $uri );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

    // Send request.
    $result = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($result);
    
    $out = array();

    if($result->responseHeader->status == 0 && isset($result->response->docs) && count($result->response->docs) > 0){
        return true;
    }else{
        return false;
    }
    
}

?>