<?php

    session_start();
    $user = $_SESSION['repo-tools-username'];

    // firstly clear the users cache directory of any existing data
    $dir_path = "files/$user";
    if(file_exists($dir_path)){
        if ($dh = opendir($dir_path)) {
              while (($file = readdir($dh)) !== false) {
                  if($file == '.') continue;
                  if($file == '..') continue;
                  if($file == 'history.txt') continue;
                  unlink("$dir_path/$file" );
              }
        }
    }else{
      mkdir($dir_path);
    }
    
    // send them back to the form
    header("Location: index.php");
    exit;


?>