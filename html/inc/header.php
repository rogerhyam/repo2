<?php
    require_once('../config.php');
    require_once('inc/functions.php');
    header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
  <head>
    <title>RBGE Repository</title>
    <link rel="stylesheet" href="style/main.css" type="text/css" />
    <link rel="stylesheet" type="text/css" href="style/cssmenu/styles.css">
    <script type="text/javascript" src="js/jquery-1.11.3.min.js"></script>
    <script src="style/cssmenu/script.js" type="text/javascript"></script>
    <script type="text/javascript" src="js/main.js"></script>
  </head>
  <body>
  <header>
    <div id='cssmenu'>
          <ul>
             <li><a href="/">RBGE Repository</a></li>
             <li><a href='/help.php' >Help</a></li>
          </ul>
    </div>
  </header>
      <div id="repo-page-wrap">
  <!-- end inc/header.php -->