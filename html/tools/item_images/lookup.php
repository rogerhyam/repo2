<?php

require_once( '../../../config.php' );

$id = @$_GET['id'];

$query = "( item_type:\"Garden Accession\" OR item_type:\"Garden Plant\" OR \"Herbarium Specimen\" )AND catalogue_number:$id";

$uri = REPO_SOLR_URI . '/query?q=' . urlencode($query) . '&rows=20&sort=' . urlencode('catalogue_number asc');

$ch = curl_init( $uri );

curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

// Send request.
$result = curl_exec($ch);
curl_close($ch);

$result = json_decode($result);

$out = array();

if($result->responseHeader->status == 0 && isset($result->response->docs)){
    
    // there should be only one - so we take the first
    $doc = $result->response->docs[0];
    
    $out['id'] =$doc->id;
    $out['ScientificName'] = get_field_value_as_string($doc, 'scientific_name_html');
    $out['Family'] = get_field_value_as_string($doc, 'family');
    $out['Collector'] = get_field_value_as_string($doc, 'creator');
    $created = get_field_value_as_string($doc, 'object_created');
    $out['LatestDateCollected'] = substr($created, 0, 10);
    
}
header('Content-Type: text/javascript');
header('Cache-Control: no-cache');
header('Pragma: no-cache');
echo json_encode($out);
exit();


?>