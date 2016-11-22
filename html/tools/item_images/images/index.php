<?php
    session_start();
    $user = $_SESSION['repo-tools-username'];
    // just pipe back the thumbnail
    header("Content-Type: image/jpeg");
    $path = "$user/Thumb_" . $_GET['image'];
    readfile($path);
?>
