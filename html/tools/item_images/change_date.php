<?php

    session_start();
    $username = $_SESSION['repo-tools-username'];
    
    $newDate = $_REQUEST['date'];
    $imageFile = $_REQUEST['image'];
    $txtFile = "images/$username/$imageFile.txt";
    
    $lines = file($txtFile);
    $lines[1] = $newDate . "\n";
    
    $out = fopen($txtFile, 'w');
    foreach($lines as $line){
        fwrite($out, $line);
    }
    fclose($out);
    


?>