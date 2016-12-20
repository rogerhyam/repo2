<?php

    require_once( '../../../config.php' );

    $user = @$_SESSION['repo-tools-username'];

    // get a list of recently added images from their history
    if(file_exists("files/$user/history.txt")){
        $file_paths = file("files/$user/history.txt");
    }else{
        $file_paths = array();
    }

    $new_history = array();

    foreach($file_paths as $file_path){

        // dodge blanks
        if(strlen(trim($file_path)) < 1) continue;

        // check if it has been indexed. If so remove it from the list if it is in the index
        if(is_in_index(trim($file_path))) continue;

        $path_parts = pathinfo($file_path);
        $extension = trim(strtolower($path_parts['extension']));
        
        if($extension == 'jpg' || $extension == 'jpeg'){
            $file_path_base64 = base64_encode(trim($file_path));
            echo "<li><img class=\"repo-recent-image\" src=\"/image_server.php?kind=100-square&path_base64=$file_path_base64\" /> {$path_parts['basename']}</li>";
        }elseif($extension == 'pdf'){
            // there should be a summary image
            $summary_image = $path_parts['dirname'] . '/' .  $path_parts['filename'] . '.json.jpg';
            if(file_exists( REPO_ROOT . $summary_image )){
                $summary_image_base64 = base64_encode($summary_image);
                echo "<li><img class=\"repo-recent-image\" src=\"/image_server.php?kind=100-square&path_base64=$summary_image_base64\" />{$path_parts['basename']}</li>";
            }else{
                echo "<li>[No Summary Image!] - {$path_parts['basename']}</li>";
            }
            
        }else{
            echo "<li>{$path_parts['basename']}</li>";
        }
        
        $new_history[] = $file_path;

    }

    // write the altered array back out
    if(!file_exists("files/$user")) mkdir("files/$user");
    file_put_contents( "files/$user/history.txt", $new_history );


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