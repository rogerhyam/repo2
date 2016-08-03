<?php

    require_once('../config.php');
    require_once('inc/functions.php');

    // load the document
    $doc = get_solr_item_by_id($_GET['id']);
    
    // body of the search result
     echo '<div class="repo-search-result-bottom">';

     $right_col_width = "auto";

     echo "<div class=\"repo-search-result-bottom-right-col\">";
     
     // insert a map if we have lat and lon
     if(isset($doc->geolocation)){
         
         $latlon = $doc->geolocation;
         // render differently if likely to be in one of the gardens
         list($lat, $lon) = explode(',', $latlon);
         if($lat > 52 && $lat < 57 && $lon < 0 && $lon > -5){
             $zoom = 14;
             $type = 'hybrid';
         }else{
             $zoom = 3;
             $type = 'roadmap';
         }
         
         $static_map_uri = "https://maps.googleapis.com/maps/api/staticmap?center=$latlon&zoom=$zoom&size=300x200&maptype=$type&markers=color:red|$latlon&key=" . REPO_GOOGLE_API_KEY;
         $dynamic_map_uri = "http://maps.google.com/?t=h&q=$latlon";
                  
         echo "<br/><a target=\"repo-google-map\" href=\"$dynamic_map_uri\"><img class=\"repo-large-static-map\" src=\"$static_map_uri\" /></a>";
         
         $right_col_width = "320px";
     }
     
     // insert image if we have one
     if(isset($doc->mime_type_s) && $doc->mime_type_s == 'image/jpeg'){
         $src = 'image_server.php?kind=300&path=' . $doc->storage_location_path;
         echo "<br/><img class=\"large-image\" src=\"$src\" />";
         $right_col_width = "320px";
     }

     echo "</div>"; // repo-search-result-bottom-right-col

     // a wrapper div for the content
     echo "<div style=\"margin-right: $right_col_width;\" class=\"repo-search-result-bottom-content\">"; //

     if(isset($doc->content)){
         echo '<div class="repo-content-field">';
         echo $doc->content;
         echo "</div>";
     }


     // a list of taxonomic fields
     echo "<h3>Taxonomy:</h3>";
     echo '<ul class="repo-field-list">';
     write_field_li($doc, 'higher_taxon', 'Higher Taxon', 'Higher Taxa');
     write_field_li($doc, 'family', 'Family', 'Families');
     write_field_li($doc, 'genus', 'Genus', 'Genera');
     write_field_li($doc, 'epithet', 'Epithet', 'Epithets');
     write_field_li($doc, 'scientific_name_html', 'Scientific Name', 'Scientific Names');
     echo '</ul>';

     echo "<h3>Geography:</h3>";
     echo '<ul class="repo-field-list">';
     write_field_li($doc, 'country_name', 'Country', 'Countries');
     write_field_li($doc, 'country_iso', 'Country Code', 'Country codes');
     write_field_li($doc, 'location', 'Location', 'locations');
     write_field_li($doc, 'geolocation', 'Lat-Lon', 'Lat-Lon', false);
     write_field_li($doc, 'elevation', 'Elevation', 'Elevations', false);
     echo '</ul>';
     
     echo "<h3>Metadata:</h3>";
     echo '<ul class="repo-field-list">';
     write_field_li($doc, 'catalogue_number', 'Catalogue number', 'Catalogue numbers');
     write_field_li($doc, 'catalogue_number_other', 'Other number', 'Other numbers');
     write_field_li($doc, 'storage_location', 'Stored', 'Stored', false);
     write_field_li($doc, 'object_created', 'Object created', 'Object created', false);
     write_field_li($doc, 'embargo_date', 'Embargoed till', 'Embargoed till', false);
     write_field_li($doc, 'indexed_at', 'Indexed', 'Indexed', false);
     echo '</ul>';

     if(isset($doc->storage_location_path) && isset($doc->storage_location) && $doc->storage_location == 'Repository'){
         echo "<h3>Download:</h3>";
         echo '<ul class="repo-field-list">';
         
         $can_download = true;
         
         if(isset($doc->embargo_date)){
             $embargo_date = new DateTime($doc->embargo_date);
             // fixme - allow them to do it within our network.
             if($embargo_date->getTimestamp() > time()){
                 $can_download = false;
             }
         }
         
         if($can_download){             
             echo "<li>";
             echo "FIXME";
             echo "</li>";
         }else{
             echo "<li>This item is still within its embargo date.</li>";
         }
         
         echo '</ul>';
         
         
     }



    
    
     // derived from
     $parent_doc = get_solr_parent_item($doc);
     if($parent_doc){
         echo "<h3>Derived from:</h3>";
         echo '<ul class="repo-item-list repo-derived-from">';
         echo  write_doc_link_li($parent_doc);
         echo '</ul>';        
     }        

     // derived items
     $child_docs = get_solr_child_items($doc);
     if($child_docs){
         echo "<h3>Derived items:</h3>";
         echo '<ul class="repo-item-list repo-derived-items">';
         foreach($child_docs as $kid){
             echo  write_doc_link_li($kid);
         }
         echo '</ul>';
     }

     // this is an annotation of
     $anno_of = get_solr_annotation_of($doc);
     if($anno_of){
         echo "<h3>Annotation of:</h3>";
         echo '<ul class="repo-item-list repo-derived-from">';
         echo  write_doc_link_li($anno_of);
         echo '</ul>';        
     }

     // annotations
     $annotations = get_solr_annotations($doc);
     if($annotations){
         echo "<h3>Annotations:</h3>";
         echo '<ul class="repo-item-list repo-annotations">';
         foreach($annotations as $anno){
             echo  write_doc_link_li($anno);
         }
         echo '</ul>';
     }

     echo '<div class="repo-search-result-bottom-footer">&nbsp;';
     echo "<pre>";
     var_dump($doc);
     echo "</pre>";
     echo '</div>';

     echo '</div>'; // repo-search-result-bottom-content

     echo '</div>'; // search result bottom
    
    
    


?>