<?php
    require_once('../../config.php');
    
    // we don't destroy the session because they may have search params stored in it
    unset($_SESSION['repo-tools-logged-in']);
    unset($_SESSION['repo-tools-user-display-name']); 
    unset($_SESSION['repo-tools-username']);
    unset($_SESSION['repo-tools-permissions']);

    header('Location: /');

?>