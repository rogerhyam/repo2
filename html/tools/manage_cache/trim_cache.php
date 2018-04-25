<?php

header('Location: index.php');

$cache_dir_path = realpath ( '../../cache' );
$days = $_GET['days'];

system("find $cache_dir_path -type f -name '*.jpg' -mtime +$days -exec rm -f {} \;");
	
exit;

?>