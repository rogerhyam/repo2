<?php

function write_facetting_link($field, $value){
    
    $qs_part = "fq=$field:$value";
    $qs = urldecode($_SERVER['QUERY_STRING']);
    if(!strpos($qs, $qs_part)){
        $new_uri = "index.php?$qs&$qs_part";
        echo "<a class=\"repo-facetting-link\" href=\"$new_uri\">$value</a>";
    }else{
        echo $value;
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
    echo "<a class=\"repo-searching-link\" href=\"index.php?$qs\">$value</a>";
    
}

function write_facet_li($doc, $field, $label_single, $label_plural){
    
    if(isset($doc->$field)){
        echo '<li>';
        
        if(is_array($doc->$field)){
            echo "<strong>$label_plural:</strong> ";
            $first = true;
            foreach($doc->$field as $val){
                write_facetting_link($field, $val);
                if(!$first){
                    echo ", ";
                }else{
                    $first = false;
                }
            }
        }else{
            echo "<strong>$label_single:</strong> ";
            write_facetting_link($field, $doc->$field);            
        }
        
        echo '</li>';
    }
    
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