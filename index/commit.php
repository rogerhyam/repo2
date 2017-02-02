<?php

require_once('../config.php');

$uri = REPO_SOLR_URI . '/update?commit=true';
$ch = curl_init( $uri );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

// Send request.
$result = curl_exec($ch);
curl_close($ch);

echo $result;

?>