<?php
    
    require_once('../config.php');

    // work out what is being included and call that
    $srv_name = @$_GET['srv_name'];
    
    switch ($srv_name){
        case 'images':
            require_once('inc/srv_images.php');
            break;
        case 'item':
            require_once('inc/srv_item.php');
            break;
        case 'sharing':
            require_once('inc/srv_sharing.php');
            break;
        default:
            header("HTTP/1.0 404 Not Found");
            echo "Unrecognised service name $srv_name";
            break;
    }
    
    


?>