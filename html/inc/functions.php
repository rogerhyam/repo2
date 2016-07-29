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