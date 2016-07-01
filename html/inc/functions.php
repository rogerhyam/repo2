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


?>