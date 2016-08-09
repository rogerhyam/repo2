<?php
    require_once('../config.php');
    require_once('inc/functions.php');

    /*
    
        returns a json summary of a images associated with catalogue number
        
        // logic here is similar to images_service.php but not so promiscuous in returning icons etc
        // a generic script to do both jobs would be far more complex than two separate ones.
        
        # http://repo.rbge.org.uk/service/images/<cat_ids>?callback=<method_name>
        RewriteRule ^service/images/([^/]*) srv.php?srv_name=images&ids=$1 [QSA,NC]
    */
    
    
    // ids contains a json array of catalogue_ids
    $ids = @$_GET['ids'];
    if($ids){
        $ids = json_decode($ids);
    }else{
        $ids = array();
    }

    $images = array();
    foreach($ids as $cat_id){
        
        // find all the ones that match that id (should only be one really)
        $result = query_solr('catalogue_number:"'.$cat_id.'"');
        
        echo "<pre>";
        print_r($result);
        echo "</pre>";
        
    }
    
    
    // If its a jsonp callback request
    /*
    $callback = check_plain($_REQUEST['callback']);
    if (isset($callback) && $callback != '') {
      $json_response = drupal_json_encode($images);
      header("Content-type: text/javascript");
      echo $callback ."(". $json_response .");";
    } else {
      drupal_json_output($images);
    }
    */
    
    


?>