<?php

require_once( '../../../config.php' );

$term = @$_GET['term'];

$query = "( item_type:\"Garden Accession\" OR item_type:\"Garden Plant\" OR \"Herbarium Specimen\" )AND catalogue_number:$term*";

$uri = REPO_SOLR_URI . '/query?q=' . urlencode($query) . '&rows=20&sort=' . urlencode('catalogue_number asc');
$ch = curl_init( $uri );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

// Send request.
$result = curl_exec($ch);
curl_close($ch);

$result = json_decode($result);

$out = array();

if($result->responseHeader->status == 0 && isset($result->response->docs)){

    foreach($result->response->docs as $doc){
        if( is_array($doc->catalogue_number) && count($doc->catalogue_number) ){
            $out[] = array('value' => $doc->catalogue_number[0] );
        }else{
            $out[] = array('value' => $doc->catalogue_number );
        }
    }

}
header('Content-Type: text/javascript');
header('Cache-Control: no-cache');
header('Pragma: no-cache');
echo json_encode($out);
exit();

?>