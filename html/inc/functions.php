<?php

function write_doc($doc, $index){
    
    // tag the 
    $type_css =  'repo-'. str_replace(' ', '-', strtolower($doc->item_type));
    $odd_even_css =  'repo-' . ($index % 2 ? 'odd' : 'even');
    
    echo "<div class=\"repo-search-result $type_css $odd_even_css\">";
    
    // top of he search result
    echo '<div class="repo-search-result-top">';
    
    echo '<div class="thumbnail">';
    if(isset($doc->mime_type_s) && $doc->mime_type_s == 'image/jpeg'){
        $src = 'image_server.php?kind=40&path=' . $doc->storage_location_path;
        echo "<img src=\"$src\" />";
    }else{
        echo "*";
    }
    echo '</div>'; // image div 
    
    // item_type
    echo '<span class="repo-item-type">';
    echo $doc->item_type;
    echo '</span>';
    
    // title
    echo "<h3>";
    echo $doc->title[0];
    echo "</h3>";            
    
    echo '</div>'; // search result top
    
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
    
    // derived from
    $parent_doc = get_solr_parent_item($doc);
    if($parent_doc){
        echo "<h4>Derived from:</h4>";
        echo '<ul class="repo-derived-from">';
        echo  write_doc_link_li($parent_doc);
        echo '</ul>';        
    }        

    // derived items
    $child_docs = get_solr_child_items($doc);
    if($child_docs){
        echo "<h4>Derived items:</h4>";
        echo '<ul class="repo-derived-items">';
        foreach($child_docs as $kid){
            echo  write_doc_link_li($kid);
        }
        echo '</ul>';
    }

    if(isset($doc->content)){
        echo '<div class="repo-content-field">';
        echo $doc->content;
        echo "</div>";
    }

    echo "<pre>";
    var_dump($doc);
    echo "</pre>";
    
    echo '</div>'; // search result bottom

    echo "</div>"; // search result
    
    
}

function write_doc_link_li($doc){
    
    $uri = '/index.php?q=' . urlencode('id:"'. $doc->id .'"') . '&repo_type=hidden&' . REPO_SOLR_QUERY_STRING;
    $title = $doc->title;
    
    echo "<li>";
    echo $doc->item_type;
    echo ": <a href=\"$uri\">";
    echo $doc->title[0];
    echo "</a>";
    echo "</li>";
    
}

function get_solr_child_items($parent){
    
    $parent_id = $parent->id;
    
    $result = query_solr("derived_from:\"$parent_id\"");
    
    if($result->responseHeader->status == 0){
        
        if($result->response->numFound < 1){
            return null;
        }
        
        return $result->response->docs;
        
    }else{
        return null;
    }
    
    
}

// get the item this item is derived from - or false
function get_solr_parent_item($child){
    
    // do nothing if we don't have a parent doc
    if(!isset($child->derived_from) || !$child->derived_from){
        return null;
    }
    
    $parent_id = $child->derived_from;

    $result = query_solr("id:\"$parent_id\"");
    
    if($result->responseHeader->status == 0){
        
        if($result->response->numFound > 1){
            echo "Warning: More than one item returned for id $id \n";
            return false;
        }
        
        if($result->response->numFound < 1){
            return null;
        }
        
        return $result->response->docs[0];
        
    }else{
        return null;
    }
    
}

function query_solr($query){
    
    $uri = REPO_SOLR_URI . '/query?q=' . urlencode($query);
    $ch = curl_init( $uri );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    
    // Send request.
    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result);

}

function write_searching_link($field, $value){
    
    // chop the current q= out of the query string
    $qs = $_SERVER['QUERY_STRING'];
    $val = urlencode(strip_tags($value));
    $matches = array($qs);
    preg_match('/(q=[^&]+)&/',$qs,$matches);
        
    if(count($matches) == 2){
        $qs = str_replace($matches[1], 'q='. $val, $qs);
    }
    
    // turn it into a simple search so the term is escaped and shown in the search box
    $qs = preg_replace('/&repo_type=[a-z_]+/', '', $qs);
    $qs .= '&repo_type=simple';
    
    echo "<a class=\"repo-searching-link\" href=\"index.php?$qs\">$value</a>";
    
}

function write_field_li($doc, $field, $label_single, $label_plural){
    
    if(isset($doc->$field)){
           echo '<li>';

           if(is_array($doc->$field)){
               echo "<strong>$label_plural:</strong> ";
               $first = true;
               foreach($doc->$field as $val){
                   write_searching_link($field, $val);
                   if(!$first){
                       echo ", ";
                   }else{
                       $first = false;
                   }
               }
           }else{
               echo "<strong>$label_single:</strong> ";
               write_searching_link($field, $doc->$field);            
           }

           echo '</li>';
      }
    
}

function write_facet_select($result, $field_name, $label, $facet_queries){
    
    // give up if there is no field by that name
    if(!isset($result->facet_counts->facet_fields->$field_name)) return;
    
    echo '<p>';
    echo "<strong>$label:&nbsp;</strong>";
    echo '<select name="fq" onchange="this.form.submit();">';
    echo '<option value="">~ Any ~</option>';
    
    for($i = 0; $i < count($result->facet_counts->facet_fields->$field_name); $i = $i + 2){
        $name = $result->facet_counts->facet_fields->$field_name[$i];
        $count = $result->facet_counts->facet_fields->$field_name[$i + 1];
        $selected = in_array($field_name . ':"' . $name . '"', $facet_queries) ? 'selected': '';
        echo "<option value=\"$field_name:&quot;$name&quot;\" $selected >$name ($count)</option>";
    }
    
    echo '</select>';
    echo '</p>';

}


?>