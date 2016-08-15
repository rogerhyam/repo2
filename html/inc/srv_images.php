<?php
    require_once('../config.php');
    require_once('inc/functions.php');

    /*
    
        returns a json summary of a images associated with catalogue number
        
        // logic here is similar to images_service.php but not so promiscuous in returning icons etc
        // a generic script to do both jobs would be far more complex than two separate ones.
        
        # http://repo.rbge.org.uk/service/images/<cat_ids>?callback=<method_name>
        RewriteRule ^service/images/([^/]*) srv.php?srv_name=images&ids=$1 [QSA,NC]
        
        
        [0] => stdClass Object (
            [setid] => Accession:20061157
            [subject_catalogue_number] => 20061157A
            [repo_image_item_uri] => http://repo.rbge.org.uk/node/964682
            [repo_subject_item_uri] => http://repo.rbge.org.uk/node/682272
            [scientific_name] => Sibbaldia procumbens L.
            [photographer] => David Knott
            [year] => 2011
            [month] => 05
            [day] => 09
            [repo_object_created_date] => 2011-05-09
            [width] => 3000
            [height] => 4000
            [url] => http://repo.rbge.org.uk/service/image/964682
            [thumbnail] => http://repo.rbge.org.uk/service/image/964682/75/square
            [medium] => http://repo.rbge.org.uk/service/image/964682/600
            [large] => http://repo.rbge.org.uk/service/image/964682/1000
            [fullsize] => http://repo.rbge.org.uk/service/image/964682/-1
        )
        
        
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
        
        $result = query_solr('catalogue_number:"'.$cat_id.'"');
        if($result->responseHeader->status == 0){
            
            foreach($result->response->docs as $doc){
                
                // create a new object for the image - like a template
                $image = new stdClass();
                $image->setid = $doc->id;
                $image->subject_catalogue_number = $cat_id;
                $image->repo_subject_item_uri = $doc->id;
                if(isset($doc->scientific_name_html)) $image->scientific_name = $doc->scientific_name_html[0];
                
                // call any derived items to add the images
                // note we use images[0] as a place holder for the current template
                $images[0] = $image;
                get_images($doc->id, $images);
                        
            
            } // each result
                
        } // got a result
    
        
    
    } // each id passed in 
    
    // remove the template from the start of the image array.
    unset($images[0]);
    $images = array_values($images); // keep array sequencial
    
/*        
    echo "<pre>";
    print_r($images);
    echo "</pre>";
*/
    // If its a jsonp callback request
    header("Content-type: text/javascript");
    $callback = @$_GET['callback'];
    if (isset($callback) && $callback != '') {
      $json_response = json_encode($images);
      echo $callback ."(". $json_response .");";
    } else {
      echo json_encode($images);
    }
    
    function get_images($parent_id, &$images){
        
        $result = query_solr('derived_from:"'.$parent_id.'"');
                
        if($result->responseHeader->status == 0){
            
            foreach($result->response->docs as $doc){
            
               // print_r($doc);
                
                // is it a jpeg? - if so add it as an image
                if( isset($doc->mime_type_s)
                    && $doc->mime_type_s == 'image/jpeg'
                    && isset($doc->storage_location)
                    && $doc->storage_location == 'Repository'
                    && isset($doc->storage_location_path)
                ){
                    
                    $image = clone $images[0];
                    
                    $image->repo_image_item_uri = $doc->id;
                    
                    if(isset($doc->creator[0])){
                        $image->photographer = $doc->creator[0];
                    }
                    
                    if(isset($doc->object_created)){
                        $created = new DateTime($doc->object_created);
                        $image->year = $created->format('Y');
                        $image->month = $created->format('m');
                        $image->day = $created->format('d');
                        $image->repo_object_created_date = $created->format('Y-m-d');
                    }
                    
                    if(isset($doc->image_height_pixels_i)){
                        $image->height = $doc->image_height_pixels_i;
                    }
                    
                    if(isset($doc->image_height_pixels_i)){
                        $image->width = $doc->image_width_pixels_i;
                    }
                    
                    
                    // links to the image
                    $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/image_server.php?path=';
                    $image->url = $base_url . $doc->storage_location_path;
                    $image->thumbnail = $base_url . $doc->storage_location_path . '&kind=75-square';
                    $image->medium = $base_url . $doc->storage_location_path . '&kind=600';
                    $image->large = $base_url . $doc->storage_location_path . '&kind=1000';
                    $image->fullsize = $base_url . $doc->storage_location_path . '&kind=original';
            
                    //$image->repo_index_doc = $doc;
                    
                    // finally add it to the list of images
                    $images[] = $image;
                    
                }else{
                    get_images($doc->id, $images);
                }
                
            } // for each result
            
        } // have a result
    
    }// get_image
    

?>