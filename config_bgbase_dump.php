<?php


    /*
    $db_host = 'elmer.rbge.org.uk';
    $db_database = 'bgbase_dump';
    $db_user = 'Mobile';
    $db_password = 'rh2508';
    */
    
    // testing
    
    $db_host = 'localhost';
    $db_database = 'bgbase_dump';
    $db_user = 'root';
    $db_password = 'yellow122';
    
    
    // create and initialise the database connection
    $mysqli = new mysqli($db_host, $db_user, $db_password, $db_database);    

    // connect to the database
    if ($mysqli->connect_error) {
    $returnObject['error'] = $mysqli->connect_error;
    sendResults($returnObject);
    }

    if (!$mysqli->set_charset("utf8")) {
        printf("Error loading character set utf8: %s\n", $mysqli->error);
    }

?>