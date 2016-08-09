<?php

function write_doc($doc, $index){
    
    // tag the 
    $type_css =  'repo-'. str_replace(' ', '-', strtolower($doc->item_type));
    $odd_even_css =  'repo-' . ($index % 2 ? 'odd' : 'even');
    
    echo "<div class=\"repo-search-result $type_css $odd_even_css\" data-repo-doc-id=\"{$doc->id}\">";
    
    // top of he search result
    echo '<div class="repo-search-result-top" >';
    
    echo '<div class="repo-image-placeholder" data-repo-image-kind="100-square" data-repo-image-height="100" data-repo-doc-id="'. $doc->id .'"></div>';
    
    // item_type
    echo '<span class="repo-item-type">';
    echo $doc->item_type;
    if(isset($doc->link_out)){
        echo " [<a href=\"{$doc->link_out}\">Link Out</a>]";
    }
    echo '</span>';
    
    // title
    echo "<h3>";
    echo $doc->title[0];
    echo "</h3>";
    
    echo '</div>'; // search result top
    echo '<div class="repo-bottom-placeholder"></div>';
    echo "</div>"; // search result
    
}

function write_doc_link_li($doc){
    
    $uri = '/index.php?q=' . urlencode('id:"'. $doc->id .'"') . '&repo_type=hidden&' . REPO_SOLR_QUERY_STRING;
    $title = $doc->title;
    
    echo "<li data-repo-doc-uri=\"$uri\">";
    echo '<div class="repo-image-placeholder" data-repo-image-kind="50-square" data-repo-image-height="50" data-repo-doc-id="'. $doc->id .'"></div>';
    echo $doc->item_type;
    echo ': <strong>' . $doc->title[0] . '</strong>';
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

function get_solr_annotations($doc){
    
    $doc_id = $doc->id;
    
    $result = query_solr("annotation_of_s:\"$doc_id\"");
    
    if($result->responseHeader->status == 0){
        
        if($result->response->numFound < 1){
            return null;
        }
        
        return $result->response->docs;
        
    }else{
        return null;
    }
    
    
}

function get_solr_annotation_of($annotation){
    
    // do nothing if we aren't an annotation
    if(!isset($annotation->annotation_of_s) || !$annotation->annotation_of_s){
        return null;
    }
    
    $doc_id = $annotation->annotation_of_s;
    
    echo $doc_id;
    
    $result = query_solr("id:\"$doc_id\"");
    
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
    
    $uri = REPO_SOLR_URI . '/query?q=' . urlencode($query) . '&rows=1000';
    $ch = curl_init( $uri );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    
    // Send request.
    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result);

}

function get_solr_item_by_id($id){
    
    $result = query_solr("id:\"$id\"");
    
    if($result->responseHeader->status == 0){
        
        if($result->response->numFound > 1){
            echo "Warning: More than one item returned for id $id \n";
            return false;
        }
        
        if($result->response->numFound < 1){
            return false;
        }
        
        return $result->response->docs[0];
        
    }else{
        return false;
    }
    
}

function write_searching_link($value){
    
    $val = urlencode(strip_tags($value));
    $uri = 'index.php?q=' . $val  . '&repo_type=simple&' . REPO_SOLR_QUERY_STRING;
    
    echo "<a class=\"repo-searching-link\" href=\"$uri\">$value</a>";
    
}

function write_field_li($doc, $field, $label_single, $label_plural, $link = true){
    
    if(isset($doc->$field)){
           echo '<li>';

           if(is_array($doc->$field)){
               echo "<strong>$label_plural:</strong> ";
               $first = true;
               foreach($doc->$field as $val){
                   if(!$first) echo ", ";
                   else $first = false;
                   if($link) write_searching_link($val);
                   else echo format_value($val);
               }
           }else{
               echo "<strong>$label_single:</strong> ";
               if($link){
                   write_searching_link($doc->$field);
               }else{
                   echo format_value($doc->$field);
               }
           }

           echo '</li>';
      }
    
}

function format_value($value){
    
    // we take the starndard string formats
    // turn them into something nice
    
    // does it look like a date?
    // is it a day date e.g. 2009-02-10T00:00:00Z
    // is it a date and time e.g. 2016-07-29T10:37:38.692Z
 
    if(preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}/', $value)){
        $dt = new DateTime($value);
        if($dt->format('H:i:s') == "00:00:00"){
            return $dt->format("Y-m-d");
        }else{
            return $dt->format("Y-m-d @ H:i");
        }
        
    }
    
    // does it look like an integer?
    if(is_numeric($value)){
        return number_format($value);
    }
    
    // not found to be a date so just return as is
    return $value;
    
}

function write_facet_select($result, $field_name, $label, $facet_queries){
    
    // give up if there is no field by that name
    if(!isset($result->facet_counts->facet_fields->$field_name)) return;
    
    echo '<p>';
    echo "<strong>$label:&nbsp;</strong>";
    echo '<select name="fq" onchange="repo.filterChange(this)">';
    echo '<option value="">~ Any ~</option>';
    
    for($i = 0; $i < count($result->facet_counts->facet_fields->$field_name); $i = $i + 2){
        $name = $result->facet_counts->facet_fields->$field_name[$i];
        $count = $result->facet_counts->facet_fields->$field_name[$i + 1];
        $count = number_format($count);
        $selected = in_array($field_name . ':"' . $name . '"', $facet_queries) ? 'selected': '';
        echo "<option value=\"$field_name:&quot;$name&quot;\" $selected >$name ($count)</option>";
    }
    
    echo '</select>';
    echo '</p>';

}

function human_filesize($bytes, $decimals = 2) {
  $sz = 'BKMGTP';
  $factor = floor((strlen($bytes) - 1) / 3);
  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}



?>