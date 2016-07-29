<?php
     require_once('../config.php');

    /*
     This will render the html to embed an thumbnail (or rotating thumbnails) for a repository item.
     It may render nothing if it doesn't find a thumbnail
     Intended to run as a ajax call on the results page
    */
    
    $item_id = @$_GET['id'];
    if(!$item_id){
        echo "<!-- no id provided for thumbnail -->";
        exit;
    }
    
    $image_locations = array();
    add_images($item_id, $image_locations, false);
    $unique_image_locations = array_unique($image_locations);
    render_images($unique_image_locations);
    
    function render_images($image_locations){
        
        // what kind of image are they looking at
        $kind = @$_GET['kind'];
        if(!$kind) $kind = '100-square';
        
        echo "<div class=\"repo-image-wrapper repo-image-wrapper_$kind\">";
        foreach($image_locations as $image_loc){
            if(preg_match('/^http/', $image_loc)){
                $src =  $image_loc;
            }else{
                $src = "image_server.php?kind=$kind&path=" . $image_loc;
            }
            
            echo "<img src=\"$src\" />";
        }
        echo '</div>'; // image div
    }
    
    function add_images($item_id, &$image_locations, $look_to_derived){
        
        if($look_to_derived){
            $uri = REPO_SOLR_URI . '/query?rows=1000&q=' . urlencode("derived_from:\"$item_id\"");
        }else{
            $uri = REPO_SOLR_URI . '/query?rows=1000&q=' . urlencode("id:\"$item_id\"");
        }
    
        $ch = curl_init( $uri );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result);
        //print_r($result);
        if($result->responseHeader->status != 0 || $result->response->numFound < 1){
            return;
        }

        foreach($result->response->docs as $doc){

            // we always add a map if we can
            if(isset($doc->geolocation) && $doc->geolocation){
                
                // we can use a map as an image if we are stuck
                // need the size
                if(@$_GET['kind']){
                    if(is_numeric($_GET['kind'])) $image_size = $_GET['kind'];
                    else list($image_size, $image_style) = explode('-', $_GET['kind']);
                }else{
                    $image_size = 100;
                }
                
                
                $image_size = $image_size ."x". $image_size;
                $latlon = $doc->geolocation;
                
                // render differently if likely to be in one of the gardens
                list($lat, $lon) = explode(',', $latlon);
                if($lat > 52 && $lat < 57 && $lon < 0 && $lon > -5){
                    $zoom = 14;
                    $type = 'hybrid';
                }else{
                    $zoom = 3;
                    $type = 'roadmap';
                } 
                
                $image_locations[] = "https://maps.googleapis.com/maps/api/staticmap?center=$latlon&zoom=$zoom&size=$image_size&maptype=$type&markers=color:red|$latlon&key=" . REPO_GOOGLE_API_KEY;
            
            }

            // then we look for other images
            if(isset($doc->summary_image_s) && $doc->summary_image_s){
                // if we have a summary image use that and don't look at derivative
                $image_locations[] = $doc->summary_image_s;
            }else if(isset($doc->mime_type_s) && $doc->mime_type_s == 'image/jpeg' && isset($doc->storage_location_path) &&  $doc->storage_location_path){
                // if actually are a jpeg image use that
                //print_r($doc);             echo "<hr>";
                $image_locations[] = $doc->storage_location_path;
            }else{
                // if we aren't renderable as an image look to derivatives
                add_images($doc->id, $image_locations, true);
            }
        }

    }
    


?>