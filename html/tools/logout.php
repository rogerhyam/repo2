<?php
    require_once('../../config.php');
    
    // we don't destroy the session because they may have search params stored in it
    $_SESSION['repo-tools-logged-in'] = false;
    $_SESSION['repo-tools-user-display-name'] = null; 
    $_SESSION['repo-tools-username'] = null;

    header('Location: /');

?>