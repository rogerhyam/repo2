<?php
    
    require_once('inc/functions.php');

    // returns results of a search for a single item with its derivatives by a field value in the appropriate format
    // e.g. http://repo.rbge.org.uk/service/item/xml/guid?id=http://data.rbge.org.uk/herb/E00137838
    // RewriteRule ^service/item/([^/]*)/([^/]*)/([^/]*) srv.php?srv_name=item&response_format=$1&field_name=$2&field_value=$3 [QSA,NC]

    // default to catalogue number
    $field_name = @$_GET['field_name'];
    if(!$field_name) $field_name = 'catalogue_number';
    
    // default to json
    $response_format = @$_GET['response_format'];
    if(!$response_format) $response_format = 'json';
    
    // default to an empty string search
    $field_value = @$_GET['field_value'];
    if(!$field_value) $field_value = '';
    
    // fetch the record(s)
    $query = $field_name . ':"' . $field_value . '"  AND -hidden_b:true';
    $response = query_solr($query);
    
    // do we get an error?
    if($response->responseHeader->status != 0){
        
       if($response->responseHeader->status >= 400){
           header("HTTP/1.0 400 Bad Request");
       }else{
           header("HTTP/1.0 500 Internal Server Error");
       }
       
       if(isset($response->error->msg)){
           echo $response->error->msg;
       }

       exit;

    }
    
    // work through the results and return the doc for each item
    $out = array();
    foreach($response->response->docs as $doc){
        // we add derived items 
        add_derivatives_recursively($doc);
        $out[] = $doc;
    }
   
    // write it out in the appropriate format
    switch ($response_format) {
        case 'json':
            header("Content-type: text/javascript");
            $callback = @$_GET['callback'];
            if ($callback) {
              $json_response = json_encode($out);
              echo $callback ."(". $json_response .");";
            } else {
              echo json_encode($out);
            }
            exit;
        case 'php':
             header("Content-type: text/plain");
             echo serialize($out);
             exit;
        default:
            header("HTTP/1.0 400 Bad Request");
            echo "Unsupported response format '$response_format'";
            exit;
    }
   
    
    function add_derivatives_recursively($doc){
        
        $response = query_solr('derived_from:"'. $doc->id .'"  AND -hidden_b:true');
              
        if($response->responseHeader->status == 0 && count($response->response->docs) > 0){
            $doc->derivatives = $response->response->docs;
            
            foreach($doc->derivatives as $derivative){
                add_derivatives_recursively($derivative);
            }    
        }
        
        
    }
    

?>