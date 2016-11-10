<?php
    
    // check they are logged in and have authorisation permission
    // FIXME
    
    $db_path = '../../../data_local/tools_permissions.db';
    $db = new SQLite3($db_path);
   
    // always remove it
    $stmt = $db->prepare("DELETE FROM tools_permissions WHERE user_name = :user AND tool_name = :tool");
    $stmt->bindValue(':user', $_GET['user_name'], SQLITE3_TEXT);
    $stmt->bindValue(':tool', $_GET['tool_name'], SQLITE3_TEXT);
    $stmt->execute();
    
    // if they ask we add it
    if(strtolower(@$_GET['add']) == 'true'){
        $stmt = $db->prepare("INSERT INTO tools_permissions (user_name, tool_name) VALUES (:user, :tool)");
        $stmt->bindValue(':user', $_GET['user_name'], SQLITE3_TEXT);
        $stmt->bindValue(':tool', $_GET['tool_name'], SQLITE3_TEXT);
        $stmt->execute();
    }
    
    header('Location: index.php');
    

?>