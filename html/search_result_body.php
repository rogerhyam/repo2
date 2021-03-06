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
         $src = 'image_server.php?kind=300&path_base64=' . base64_encode($doc->storage_location_path);
         $src_big = 'image_server.php?kind=1000&path_base64=' . base64_encode($doc->storage_location_path);
         echo "<br/><a data-repo-image-src=\"$src_big\" class=\"repo-image-dialogue-link\" ><img class=\"large-image\" src=\"$src\" /></a>";
         $right_col_width = "320px";
     }

     if(isset($doc->summary_image_s)){
         $src = 'image_server.php?kind=300&path_base64=' . base64_encode($doc->summary_image_s);
         $src_big = 'image_server.php?kind=1000&path_base64=' . base64_encode($doc->summary_image_s);
         echo "<br/><a data-repo-image-src=\"$src_big\" class=\"repo-image-dialogue-link\" ><img class=\"repo-large-image\" src=\"$src\" /></a>";
         $right_col_width = "320px";
     }

     echo "</div>"; // repo-search-result-bottom-right-col

     // a wrapper div for the content
     echo "<div style=\"margin-right: $right_col_width;\" class=\"repo-search-result-bottom-content\">"; //

     if(isset($doc->hidden_b) && $doc->hidden_b){
         echo "<hr/>";
         echo "<p><strong>~ Hidden ~ </strong> This item has been suppressed in the index and will not be returned in searches or published through web services</p>";
         echo "<hr/>";
     }

     if(isset($doc->content)){
         echo "<h3>Description:</h3>";
         echo '<div class="repo-content-field">';
         echo nl2br($doc->content);
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
     
     echo "<h3>Item Metadata:</h3>";
     echo '<ul class="repo-field-list">';
     write_field_li($doc, 'creator', 'Created by', 'Created by', false);
     write_field_li($doc, 'object_created', 'Created on', 'Created on', false);
     write_field_li($doc, 'catalogue_number', 'Catalogue number', 'Catalogue numbers');
     write_field_li($doc, 'catalogue_number_other', 'Other identifiers', 'Other identifiers');
     write_field_li($doc, 'image_width_pixels_i', 'Width (pixels)', 'Width (pixels)', false);
     write_field_li($doc, 'image_height_pixels_i', 'Height (pixels)', 'Height (pixels)', false);
     write_field_li($doc, 'keywords_ss', 'Keyword', 'Keywords', false);
     write_field_li($doc, 'embargo_date', 'Embargoed till', 'Embargoed till', false);
     write_field_li($doc, 'copyright_s', 'Copyright', 'Copyright', false);
     echo '</ul>';

     echo "<h3>Index Metadata:</h3>";
     echo '<ul class="repo-field-list">';
     write_field_li($doc, 'submitted_by_s', 'Submitted by', 'Submitted by', false);
     write_field_li($doc, 'submitted_on_dt', 'Submitted on', 'Submitted on', false);
     write_field_li($doc, 'indexed_at', 'Indexed', 'Indexed', false);
     write_field_li($doc, 'storage_location_garden_s', 'Garden', 'Gardens', false);
     write_field_li($doc, 'storage_location', 'Managed by', 'Managed', false);
     write_field_li($doc, 'storage_location_path', 'Path', 'Paths', false);
     write_field_li($doc, 'storage_location_description_s', 'Find', 'Find', false);
     echo '</ul>';

     if(
        isset($doc->storage_location_path)
        && isset($doc->storage_location)
        && $doc->storage_location == 'Repository'
        && (!isset($doc->mime_type_s) || $doc->mime_type_s != 'directory')        
        ){
         
         echo "<h3>Download:</h3>";
         echo '<ul class="repo-field-list">';
         
        // how big is it
        $size = filesize(REPO_ROOT . $doc->storage_location_path);
        echo "<li>";
        echo "<strong>Size:</strong> " . human_filesize($size);
        echo "</li>";

        // what is it
        if(isset($doc->mime_type_s)){
            echo "<li>";
            echo "<strong>Kind:</strong> " . $doc->mime_type_s;
            echo "</li>";
        }

        // here is the file
        $can_download = true;
        
        // this logic will be repeated in download.php - more or less
        if(isset($doc->embargo_date)){
            $embargo_date = new DateTime($doc->embargo_date);
            echo "<li><strong>Embargo date: </strong>" . $embargo_date->format('Y-m-d') . "</li>";
            
            // fixme - allow them to do it within our network.
            if($embargo_date->getTimestamp() > time()){
                $can_download = false;
                echo "<li><strong>Embargoed: </strong> This item is still within its embargo date.</li>";
            }
            
        }
           
        $parts = pathinfo($doc->storage_location_path);
        $file_name = $parts['basename'];
        echo "<li>";
        echo "<strong>File:</strong> ";
        if($can_download){
            
            echo "<a href=\"download.php?path={$doc->storage_location_path}\" >$file_name</a>";
            
        }else{
            echo $file_name;
        }
        
        echo '<button class="repo-sharing-button" data-repo-days="30" data-repo-path="'. base64_encode($doc->storage_location_path) .'">&#x25bc; 30d</button>';
        echo '<button class="repo-sharing-button" data-repo-days="-1" data-repo-path="'. base64_encode($doc->storage_location_path) .'">&#x25bc; &#x221e;</button>';
        
        echo "</li>";
 
        echo '</ul>';
         
         // end download
         
         
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

     
     echo '<div class="repo-search-result-bottom-footer">';
     if(has_permission('spot_edit')){
         echo '<a target="spot_edit" href="/tools/spot_edit/index.php?data_location='. base64_encode($doc->data_location)  .'&id='. base64_encode($doc->id) .'">&#9998;</a>';
     }
     echo '</div>';

     echo '</div>'; // repo-search-result-bottom-content

     echo '</div>'; // search result bottom
    
    
    


?>