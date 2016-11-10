<?php
    require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . '/../config.php');
    require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . '/inc/functions.php');
    header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
  <head>
    <title>RBGE Repository</title>
    
    <link rel="stylesheet" href="/style/cssmenu/styles.css" type="text/css" >
    <link rel="stylesheet" href="/js/jquery-ui.min.css"     type="text/css" media="screen" title="no title" charset="utf-8">
    <link rel="stylesheet" href="/style/main.css"           type="text/css" />
    <link rel="stylesheet" href="/style/tools.css"          type="text/css" />
    
    <script src="/js/jquery-1.11.3.min.js"  type="text/javascript"></script>
    <script src="/js/jquery-ui.min.js" type="text/javascript" charset="utf-8"></script>
    
    <script src="/style/cssmenu/script.js"  type="text/javascript"></script>
    <script src="/js/main.js"               type="text/javascript" ></script>    
    <script src="/tools/tools.js" type="text/javascript" charset="utf-8"></script>
    
  </head>
  <body>
  <header>
    <div id='cssmenu'>
          <ul>
             <li><a href="/">RBGE Repository</a></li>
             <li><a href='/help.php' >Help</a></li>
             <li><a href='/tools/index.php' >Tools</a></li>
             <?php if(@$_SESSION['repo-tools-logged-in']){
                echo "<li><a href='/tools/logout.php' >";
                echo $_SESSION['repo-tools-user-display-name'];
                echo " <span style=\"color:red\">&#x2716;</span></a></li>";
             }
             ?>
             
          </ul>
    </div>
  </header>
      <div id="repo-page-wrap">
  <!-- end inc/header.php -->