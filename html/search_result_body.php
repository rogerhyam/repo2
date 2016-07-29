<?php

    require_once('../config.php');
    require_once('inc/functions.php');

    // load the document
    $doc = get_solr_item_by_id($_GET['id']);
    
    // body of the search result
     echo '<div class="repo-search-result-bottom">';

     if(isset($doc->mime_type_s) && $doc->mime_type_s == 'image/jpeg'){
         $src = 'image_server.php?kind=400&path=' . $doc->storage_location_path;
         echo "<img class=\"large-image\" src=\"$src\" />";
     }

     // a list of taxonomic fields
     echo '<div class="repo-taxon-fields">';
     echo "<h4>Taxonomy:</h4>";
     echo '<ul>';
     write_field_li($doc, 'higher_taxon', 'Higher Taxon', 'Higher Taxa');
     write_field_li($doc, 'family', 'Family', 'Families');
     write_field_li($doc, 'genus', 'Genus', 'Genera');
     write_field_li($doc, 'epithet', 'Epithet', 'Epithets');
     write_field_li($doc, 'scientific_name_html', 'Scientific Name', 'Scientific Names');
     echo '</ul>';
     echo "</div>";

     echo '<div class="repo-geography-fields">';
     echo "<h4>Geography:</h4>";
     echo '<ul>';
     write_field_li($doc, 'country_name', 'Country', 'Countries');
     write_field_li($doc, 'country_iso', 'Country Code', 'Country codes');
     write_field_li($doc, 'location', 'Location', 'locations');
     write_field_li($doc, 'elevation', 'Elevation', 'Elevations');
     echo '</ul>';
     echo "</div>";

     if(isset($doc->content)){
         echo '<div class="repo-item-list repo-content-field">';
         echo $doc->content;
         echo "</div>";
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

     echo '</div>'; // search result bottom
    
    
    


?>