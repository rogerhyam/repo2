<?php

date_default_timezone_set('UTC');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include('../../repo_secrets.php');

$repo_query =  REPO_SOLR_URI 
    . '/query?start=0&rows=100&sort=indexed_at+asc&q='
    . urlencode('genus:Rhododendron AND (item_type:"Specimen Photo" OR item_type:"Accession Photo") AND mime_type_s:"image/jpeg" ');

echo $repo_query;

$out = fopen('rhododendron.csv', 'w');

$header = array(
"id",
"ObsId",
"FileName",
"Plantspecies",
"Plantspecies_epithet",
"Plantgenus",
"PlantFamily",
// "wfoTaxonId",
"Date",
"OrganType",
"LocalityName",
"Long",
"Lat",
"Author",
"Licence"
);

fputcsv($out, $header);

while(true){

    $ch = curl_init($repo_query);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt($ch, CURLOPT_USERPWD, REPO_SOLR_USER . ":" . REPO_SOLR_PASSWORD );
    $result = json_decode(curl_exec($ch));
    curl_close($ch);

    // get out of here if we don't have any more results
    if(count($result->response->docs) < 1 ) break;

    // work through the results in this page
    foreach($result->response->docs as $doc){

        $line = array();

        // id
        $line[] = base64_encode($doc->id);
         
        // ObsId
        if(!isset($doc->catalogue_number)){
            if(isset($doc->derived_from)){
                $parts = explode('/', $doc->derived_from);
                $obs_id = array_pop($parts);
                $line[] = $obs_id;
            }else{
                // we can't get a obsId so skip this one
                continue;
            }            
        }else{
            $line[] = $doc->catalogue_number;
        }
        
        // FileName
        $line[] =  'http://solr.rbge.info/plantnet' . $doc->storage_location_path;
        
        // Plantspecies
        $name = isset($doc->scientific_name_plain) ? implode(' ', $doc->scientific_name_plain) : "";
        $line[] = $name;
    
        // Plantspecies_epithet
        $line[] = isset($doc->epithet) ? implode(',', $doc->epithet) : "";
        
        // Plantgenus
        $line[] = isset($doc->genus) ?  implode(',', $doc->genus) : "";

        // PlantFamily
        $line[] = isset($doc->family) ?  implode(',', $doc->family) : "";
        
        // wfo id
        //$name = isset($doc->genus) ? implode(' ', $doc->genus) : "";
        //$name .=  isset($doc->epithet) ? " " . implode(' ', $doc->epithet) : "";
        //$line[] = get_wfo_id($name);

        // Date
        $line[] = isset($doc->object_created) ? $doc->object_created : "";

        // OrganType
        $line[] = "";

        // LocalityName
        $line[] = isset($doc->country_iso) ? implode(',',$doc->country_iso) : "";

        // Long	
        $line[] = "";

        // Lat
        $line[] = "";

        $authors = "Royal Botanic Garden Edinburgh";
        if(isset($doc->creator) && $doc->creator){
            $authors .= ": " . implode(',', $doc->creator);
        }
        // Author
        $line[] = $authors;

        // Licence
        $line[] = "© FIXME";

        fputcsv($out, $line);

    }

    // set up the next query to run
    $rows = $result->responseHeader->params->rows;
    $start = $result->responseHeader->params->start;
    $new_start = $start + $rows;
    $repo_query = str_replace("?start=$start&","?start=$new_start&", $repo_query);
    echo "$rows from $start\n";



}

fclose($out);
echo "All Done";


function get_wfo_id($name){

    $query = "
    query{
    taxonNameMatch(name: \"$name\"){
        currentPreferredUsage{
            guid
        }
    }
    }";
   // echo $query;
    $variables = array();

    $json = json_encode(['query' => $query, 'variables' => $variables]);

    $chObj = curl_init();
    curl_setopt($chObj, CURLOPT_URL, 'https://list.worldfloraonline.org/gql');
    curl_setopt($chObj, CURLOPT_RETURNTRANSFER, true);    
    curl_setopt($chObj, CURLOPT_CUSTOMREQUEST, 'POST');
    //curl_setopt($chObj, CURLOPT_HEADER, true);
    //curl_setopt($chObj, CURLOPT_VERBOSE, true);
    curl_setopt($chObj, CURLOPT_POSTFIELDS, $json);

    $response = curl_exec($chObj);
    $response = json_decode($response);

   // print_r($response);

    if(isset($response->data)){
        if(count($response->data->taxonNameMatch) == 1){
            return $response->data->taxonNameMatch[0]->currentPreferredUsage->guid;
        }
    }

    return "";


}