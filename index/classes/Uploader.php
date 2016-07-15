<?php

// gathers together documents to be updated in the index
// either automatically triggers submission after X docs
// or when prompted
class Uploader{
    
    var $docs;
    var $max_docs;
    var $commitWithin = 600000; // ten mintues;
    
    function __construct($max_docs = -1){
        $this->docs = array();
        $this->max_docs = $max_docs;
    }
    
    /**
     * Accepts a single JSON document object ready 
     * to be sent to Solr 
    */
    function add_document($doc){
        $this->docs[] = $doc;
        
        // submit if we have hit the limit
        if($this->max_docs != -1 && count($this->docs) >= $this->max_docs){
            $this->submit_now();
        }
    }
    
    // submits to solr
    function submit_now(){
        
        $ch = curl_init( REPO_SOLR_URI . '/update/json?commitWithin=' . $this->commitWithin );
        
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $this->docs )); // not sure why this doesn't need a field name
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        
        // Send request.
        $result = curl_exec($ch);
        curl_close($ch);
    
        // check out the response
        echo $result;
    
        // on success clear the docs down
        $this->docs = array();
    
    }
    
}


?>