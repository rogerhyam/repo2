<?php

include('../../repo_secrets.php');

// define the selectors and field lists for the dump

$item_types = array();
$item_types['Accession\ Photo'] = array(
        "id",
        "catalogue_number",
        "catalogue_number_other",
        "object_created",
        "object_created_year",
        "creator",
        "image_width_pixels_i",
        "image_height_pixels_i",
        "mime_type_s",
        "submitted_by_s",
        "submitted_on_dt",
        "item_type",
        "derived_from",
        "derivation_rank_i",
        "storage_location",
        "storage_location_path",
        "data_location",
        "indexed_at",
        "family",
        "genus",
        "epithet",
        "country_iso",
        "country_name",
        "scientific_name_html",
        "collector_id_s",
        "collector_number_s",
        "scientific_name_html_str",
        "scientific_name_plain",
        "_version_",
        "title"
);

$item_types['Specimen\ Photo'] = array(
        "id",
        "catalogue_number",
        "catalogue_number_other",
        "object_created",
        "object_created_year",
        "creator",
        "image_height_pixels_i",
        "image_width_pixels_i",
        "mime_type_s",
        "submitted_by_s",
        "submitted_on_dt",
        "item_type",
        "derived_from",
        "derivation_rank_i",
        "storage_location",
        "storage_location_path",
        "data_location",
        "indexed_at",
        "family",
        "genus",
        "epithet",
        "country_iso",
        "country_name",
        "scientific_name_html",
        "collector_id_s",
        "collector_number_s",
        "scientific_name_html_str",
        "scientific_name_plain",
        "_version_",
        "title",
);

$item_types['Image'] = array(
        "id",
        "storage_location",
        "storage_location_path",
        "item_type",
        "mime_type_s",
        "exif_json_s",
        "exif_txt",
        "iptc_json_s",
        "iptc_txt",
        "family",
        "keywords_ss",
        "genus",
        "epithet",
        "source_s",
        "content",
        "data_location",
        "indexed_at",
        "_version_",
        "title"
);

$item_types['Document'] = array(
        "id",
        "item_type",
        "data_location",
        "summary_image_s",
        "storage_location",
        "storage_location_path",
        "derived_from",
        "mime_type_s",
        "object_created_year",
        "indexed_at",
        "_version_",
        "title"
        // extracted_text is omitted because it is too long
);

foreach ($item_types as $type => $fields) {

    // open a file to write to
    $out = fopen( str_replace('\ ', "_", $type) . '.csv', 'w');

    fputcsv($out, $fields);

    $repo_query =  REPO_SOLR_URI 
    . '/query?start=0&rows=1000&fq=item_type:'
    . urlencode($type)
    .'&q=*:*';

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

            foreach ($fields as $field) {
                if(isset($doc->{$field})){
                    if(is_array($doc->{$field})){
                        $line[] = implode('|', $doc->{$field});
                    }else{
                        $line[] = $doc->{$field};
                    }
                }else{
                    $line[] = null;
                }
            }

            
            fputcsv($out, $line);

        }// foreach doc

        // set up the next query to run
        $rows = $result->responseHeader->params->rows;
        $start = $result->responseHeader->params->start;
        $new_start = $start + $rows;
        $repo_query = str_replace("?start=$start&","?start=$new_start&", $repo_query);
        echo "$type: $rows from $start\n";

    } // end paging

    // close that file
    fclose($out);
}

// create a readme file
$out = fopen( 'README.txt', 'w');
fwrite($out, "This is a dump of the contents of botanics digital repository index.\n");
fwrite($out, "It includes index records for all items that are stored in the repository but not those that are just indexed by it (e.g. BGBASE records).\n");
fwrite($out, "The IDs used in the index are HTTP URIs but they may not be resolvable. Often the column of interest will be he derived_from column as it is the link plant or accession.\n");
fwrite($out, "Created: " . date(DATE_ATOM));
fclose($out);

$zip = new ZipArchive();
$zip->open('repo_index_dump.zip', ZipArchive::CREATE);
$zip->addFile('README.txt');

foreach ($item_types as $type => $fields) {
    $file = str_replace('\ ', "_", $type) . ".csv";
    $zip->addFile($file);
}
$zip->close();
    
foreach ($item_types as $type => $fields) {
    $file = str_replace('\ ', "_", $type) . ".csv";
    unlink($file);
}
unlink('README.txt');

echo "\nAll done now!\n";



