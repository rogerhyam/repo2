<?php

    /*
        AJAX called to generate an externally shareable link used by inc/sharing.php
        
        2 arguments are passed:
            - repo_path to the file to be downloaded
            - days duration for link to last (-1 for infinite)
        
    */ 
    
    $repo_path  = @$_GET['repo_path'];
    if(!$repo_path){
        echo "Error: No path supplied";
        exit;
    }
    
    $days  = @$_GET['days'];
    if(!$days){
        echo "Error: Duration supplied";
        exit;
    }
    
    // round to the end of a day
    if($days > -1){
        $date = new DateTime();
        $date->add(new DateInterval('P'.$days.'D'));
        $date->setTime(23,59,59);
        $expires = $date->getTimestamp();
    }else{
        $expires = -1;
    }
    
    // we use a little database to handle this!
    $db_path = '../data_local/sharing_links.db';
    $db = new SQLite3($db_path);
    
    // make sure it has a table in it
    $db->exec("CREATE TABLE IF NOT EXISTS sharing_links (path_base64 TEXT, key TEXT, expires INTEGER DEFAULT 0, created DATETIME DEFAULT CURRENT_TIMESTAMP );");
    
    // does a link already exist with these params
    $result = $db->query("SELECT key FROM sharing_links WHERE path_base64 = '$repo_path' AND expires = $expires");   
    $row = $result->fetchArray(SQLITE3_ASSOC);     
    if(!$row){       
        // need to generate a key and insert it in the db
        $key = md5(uniqid($repo_path));
        $db->query("INSERT INTO sharing_links (path_base64, key, expires) VALUES ('$repo_path', '$key', $expires)");     
    }else{
        $key = $row['key'];
    }
    
    echo "http://" . $_SERVER['HTTP_HOST'] . "/service/download/$repo_path/" . $key;    
   
    
?>