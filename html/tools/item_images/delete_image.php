<?php

session_start();

$userName = $_SESSION['repo-tools-username'];

$response = array();
$response['errors'] = false;

$meta = file("images/$userName/" . $_GET['id'] . ".txt");
$response['message'] =  $_GET['id'];

// delete the requested file
try{
    
    unlink("images/$userName/" . $_GET['id']);
    unlink("images/$userName/" . $_GET['id'] . ".txt");
    unlink("images/$userName/Thumb_" . $_GET['id']);

}catch(Exception $e){
    $response['errors'] = true;
    $response['message'] = $e->getMessage();
}

header('Content-Type: text/javascript');
header('Cache-Control: no-cache');
header('Pragma: no-cache');
echo json_encode($response);
exit();


?>