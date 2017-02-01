<?php

include_once('classes/BaseAugmenter.php');
include_once('classes/IndexQueueMySQL.php');

/*
    This implements derivation relationships.
    
    When something is saved with a "derived_from" id then it populates empty fields from the thing it is derived from.
    
    When ANYTHING is saved we look for those things that are derived from it and if their fields are different queues them to be re-indexed.

*/

class DerivedItemAugmenter extends BaseAugmenter
{

    var $queue;
    
    // fields that propopogate from parents to derived items
    var $derived_fields = array(
        'higher_taxon',
        'family',
        'genus',
        'epithet',
        'country_iso',
        'country_name',
        'scientific_name_plain',
        'scientific_name_html',
        'collector_id_s',
        'collector_number_s'
        
    );
    
    
    // fields that propagate from annotations to targets
    var $annotated_fields = array(
        'higher_taxon',
        'family',
        'genus',
        'epithet',
        'geolocation',
        'country_iso',
        'country_name',
        'scientific_name_plain',
        'scientific_name_html'
    );
    
    public function __construct(){
        parent::__construct();
        $this->queue = new IndexQueueMySQL('derived_items');
    }

    public function augment($doc){
        
        // FIXME - CHECK DERIVATION RANK!
        // FIXME - ANNOTATIONS CAN'T ANNOTATE ANNOTATIONS!
        
        if(isset($doc->derived_from) && $doc->derived_from){
            $this->inherit_field_data($doc->derived_from, $doc);
        }
        
        // called on all items that might have derivatives
        $this->check_derivatives($doc);
        
        // if this is an annotation then the thing it annotates might need to be queued for re-indexing
        if(isset($doc->annotation_of_s) && $doc->annotation_of_s && isset($doc->annotation_updates_b) && $doc->annotation_updates_b){            
            $this->queue_annotation_target($doc);
        }else{
            $this->update_with_annotations($doc);
        }
    
    }
    
    public function inherit_field_data($source_id, $target){
        
        // get the item for the source
        $source = $this->get_solr_item_by_id($source_id);
        
        foreach($this->derived_fields as $field_name){
            
            // if the field isn't in the source we do nothing - no data to copy
            if(!isset($source->$field_name)) continue;

            // if the field isn't set in target add it
            // also if it equates to false (empty string or array etc)
            // we never overwrite field data
            if(!isset($target->$field_name) || !$target->$field_name){
                $target->$field_name = $source->$field_name;
            }
            
        }
        


    }
    
    public function check_derivatives($source){
        
        $source_id = $source->id;
        
        // find all the things that have my id in their "derived_from" field
        $result = $this->query_solr("derived_from:\"$source_id\"");
        
        if(isset($result->response->numFound) && $result->response->numFound > 0){
            
            // work through all the derived items
            foreach($result->response->docs as $target){
                
                // work through the heritable fields
                foreach($this->derived_fields as $field_name){
                    
                    // if the field isn't in the source we do nothing - no data to copy
                    if(!isset($source->$field_name)) continue;
                    
                    // if the target property doesn't exist we create it empty
                    if(!isset($target->$field_name)) $target->$field_name = "";
                    
                   // echo $source->$field_name . ":" .$target->$field_name. "\n";
                    
                    // if the values in fields are different queue for indexing
                    if($target->$field_name != $source->$field_name){
                        // echo $source->$field_name . ":" .$target->$field_name. "\n";
                        $this->queue->enqueue($target->id, $target->data_location);
                        break;
                    }
                    
                } // end fields
                
            } // end items
        }
        
        // check if they have the same values in their fields
        
        // if it fails any tests add it to the re-index list
        // $this->queue->enqueue($item_id, $data_file);
        
        
    }
    
    /*
    *   will queue the target of an annotation if the fields are different
    */
    public function queue_annotation_target($source){
        
        $target = $this->get_solr_item_by_id($source->annotation_of_s);
        
        if(!$target){
            echo "Can't find annotation target: " . $source->annotation_of_s . "\n";
            return;
        }
        
        // work through the heritable fields
        foreach($this->annotated_fields as $field_name){
            
            // if the field isn't in the source we do nothing - no data to copy
            if(!isset($source->$field_name)) continue;
            
            // if the target property doesn't exist we create it empty
            if(!isset($target->$field_name)) $target->$field_name = "";
            
            // if the values in fields are different queue for indexing
            if($target->$field_name != $source->$field_name){
                $this->queue->enqueue($target->id, $target->data_location);
                break;
            }
            
        } // end fields
                
    }
    
    
    public function update_with_annotations($target){
        
        // get all the annotations of this document that are set to over write stuff
        // in date order i.e. the more recent ones will overwrite the older ones 
        $result = $this->query_solr("annotation_of_s:\"{$target->id}\" && annotation_updates_b:\"true\"", "object_created ASC");
           
        if(isset($result->response->numFound) && $result->response->numFound > 0){
            
            // work through all the annotations items
            foreach($result->response->docs as $source){
                
//                echo "source_id: {$source->id}\n";
                
                foreach($this->annotated_fields as $field_name){
                
                    // if the field isn't in the source we do nothing - no data to copy
                    if(!isset($source->$field_name)) continue;
                
                    // otherwise we copy it - annotations overwrite existing data
                    $target->$field_name = $source->$field_name;
                    
                }
                
            }
            
        }
    
    
    }

}

?>