<?php

    require_once('../config.php');

    /*
        called by rewritten paths for externally sharing files
        
        RewriteRule ^service/download/([^/]*)/([^/]*) srv.php?srv_name=sharing&path_base64=$1&key=$2 [QSA,NC]
        
    */    
    
    $db_path = REPO_ROOT . '/housekeeping/sharing_links.db';
    $db = new SQLite3($db_path);
    
    // an in date key must exit
    $stmt = $db->prepare("SELECT * FROM sharing_links WHERE path_base64 = :path_base64 AND key = :key AND (expires > :now OR expires = -1 )");
    $stmt->bindValue(':path_base64', $_GET['path_base64'], SQLITE3_TEXT);
    $stmt->bindValue(':key', $_GET['key'], SQLITE3_TEXT);
    $stmt->bindValue(':now', time(), SQLITE3_TEXT);
    $result = $stmt->execute();
    
    // no result so turn them away
    if(!$result->fetchArray()){
        header("HTTP/1.0 404 Not Found");
        echo "No resource was found for this download URI. Has it expired?";
        exit;
    }
    
    $file_path = REPO_ROOT . base64_decode($_GET['path_base64']);
    $parts = pathinfo($file_path);
    
    header('Content-Description: File Transfer');
    header('Content-Disposition: attachment; filename="' . $parts['basename'] . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));
    readfile($file_path);
    
    exit;
    

?>