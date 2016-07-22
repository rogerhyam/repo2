<?php

class BaseAugmenter
{

    public function __construct(){
        // just incase we need it
    }

    protected function query_solr($query, $sort = false){
        
        $uri = REPO_SOLR_URI . '/query?q=' . urlencode($query);
        
        if($sort){
            $uri .= "&sort=" . urlencode($sort);
        }
        
        $ch = curl_init( $uri );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        
        // Send request.
        $result = curl_exec($ch);
        curl_close($ch);
    
        return json_decode($result);
    
    }
    
    protected function get_solr_item_by_id($id){
        
        $result = $this->query_solr("id:\"$id\"");
        
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
    

}


?>