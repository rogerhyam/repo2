<?php

include_once('classes/BaseAugmenter.php');
include_once('classes/IndexQueue.php');

/*
    This implements derivation relationships.
    
    When something is saved with a "derived_from" id then it populates empty fields from the thing it is derived from.
    
    When ANYTHING is saved we look for those things that are derived from it and if their fields are different queues them to be re-indexed.

*/

class DerivedItemAugmenter extends BaseAugmenter
{

    var $queue;
    
    var $fields = array(
        'higher_taxon',
        'family',
        'genus',
        'epithet',
        'geolocation',
        'country_iso',
        'country_name'
    );
    
    public function __construct(){
        parent::__construct();
        $this->queue = new IndexQueue('derived_items');
    }

    public function augment($doc){
        
        if(isset($doc->derived_from) && $doc->derived_from){
            $this->inherit_field_data($doc->derived_from, $doc);
        }
        
        $this->check_derivatives($doc);
    
    }
    
    public function inherit_field_data($source_id, $target){
        
        // get the item for the source
        $source = $this->get_solr_item_by_id($source_id);
        
        foreach($this->fields as $field_name){
            
            // if the field isn't in the source we do nothing - no data to copy
            if(!isset($source->$field_name)) continue;

            // if the field isn't set in target add it
            // also if it equates to false (empty string or array etc)
            // we never overwrite field data
            if(!isset($target->$field_name) || !$target->$field_name){
                $target->$field_name = $source->$field_name;
            }
            
        }

        // give up if we can't find it        
        
    }
    
    
    public function check_derivatives($source_doc){
        
        $source_id = $source_doc->id;
        
        // find all the things that have my id in their "derived_from" field
        $result = $this->query_solr("derived_from:\"$source_id\"");
        
        if(isset($result->response->numFound) && $result->response->numFound > 0){
            
            // work through all the derived items
            foreach($result->response->docs as $target){
                
                // work through the heritable fields
                foreach($this->fields as $field_name){
                    
                    // if the field isn't in the source we do nothing - no data to copy
                    if(!isset($source->$field_name)) continue;
                    
                    // if the values in fields are different queue for indexing
                    if($target->$field_name != $source->$field_name){
                        $this->queue->enqueue($target->id, $target->data_location);
                        break;
                    }
                    
                } // end fields
                
                echo "  no field changed\n";
                
            } // end items
        }
        
        // check if they have the same values in their fields
        
        // if it fails any tests add it to the re-index list
        // $this->queue->enqueue($item_id, $data_file);
        
        
    }

}

?>