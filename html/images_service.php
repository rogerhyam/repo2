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
    
    // if we don't have any images add a default one
    if(count($image_locations) == 0){
        
        if($root_item){
            $svg = get_default_icon($root_item->item_type, '#ffffff');
        }else{
            $svg = get_default_icon(null, '#ffffff');
        }

        $uri = 'data:image/svg+xml;charset=utf-8;base64,' . base64_encode($svg);
        $image_locations[] = "$uri";
    
    }
    
    $unique_image_locations = array_unique($image_locations);
    
    render_images($unique_image_locations);
    
    function render_images($image_locations){
        
        // what kind of image are they looking at
        $kind = @$_GET['kind'];
        if(!$kind) $kind = '100-square';
        
        echo "<div class=\"repo-image-wrapper repo-image-wrapper_$kind\">";
        foreach($image_locations as $image_loc){
            
            if(preg_match('/^http/', $image_loc) || preg_match('/^data:image/', $image_loc)){
                // it is a proper image path e.g. static map
                $src =  $image_loc;
            }else{
                // it is a path within the repo
                $src = "image_server.jpg?kind=$kind&path=" . $image_loc;
            }
            
            echo "<img src=\"$src\" />";
        }
        echo '</div>'; // image div
    }
    
    function add_images($item_id, &$image_locations, $look_to_derived){
        
        global $root_item;
        
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
        
        // didn't find anything so give up
        if($result->responseHeader->status != 0 || $result->response->numFound < 1){
            return;
        }

        foreach($result->response->docs as $doc){

            // we got something. If this is the first (root) item in the search tree 
            // we save it to a global variable
            if(!$look_to_derived) $root_item = $doc;

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
    
    function get_default_icon($icon_type = false, $colour = '#000000'){

        $map_pin = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="-8 -8 48 48" id="gel-icon-map">
            <path fill="'. $colour .'" d="M25.1 17.2c1.2-1.8 1.9-3.9 1.9-6.2 0-6.1-4.9-11-11-11S5 4.9 5 11c0 2.3.7 4.4 1.9 6.2L16 32l9.1-14.8zM16 7c2.2 0 4 1.8 4 4s-1.8 4-4 4-4-1.8-4-4 1.8-4 4-4z"/>
            </svg>';

        $document = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="-8 -8 48 48" id="gel-icon-document">
            <path fill="'. $colour .'" d="M21.3 3L27 8.7V29H5V3h16.3m.7-3H2v32h28V8l-8-8z"/>
            <path fill="'. $colour .'" d="M10 13h12v3H10zm0 4h12v3H10zm0 4h12v3H10z"/>
            </svg>';
            
        $accession = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="-8 -8 48 48" id="gel-icon-document">
                <path stroke="null" id="svg_2" d="m16.500004,6.383532l0,-5.383533l-3.300001,2.873751l-3.300001,-2.873751l-3.300001,2.873751l-3.300001,-2.873751l0,5.383533c0,4.717345 2.6082,7.279377 5.924401,7.678971l0.0756,0l0,3.914001l-7.800001,-4.474501l0,2.496126l6.998401,4.001876l2.0016,0l0,-5.939877c3.349801,-0.35625 6.000001,-2.930751 6.000001,-7.67719l0,0.000594z" fill="'. $colour .'"/>
                  <path stroke="null" transform="rotate(-2.8006298542022705 24.667501449584996,28.059501647949286) " id="svg_3" d="m21.390268,28.563461l0,1.98267l6.554467,-2.988695l0,-1.984565" fill="'. $colour .'"/>
                  <path stroke="null" id="svg_4" d="m26.750001,18.208512l0,-4.958517l-3.190001,2.646875l-3.190001,-2.646875l-3.190001,2.646875l-3.190001,-2.646875l0,4.958517c0,4.344923 2.521261,6.704689 5.726922,7.072736l0.07308,0l0,3.605001l-7.540002,-4.121251l0,2.299063l6.765122,3.685938l1.93488,0l0,-5.470939c3.238141,-0.328124 5.800002,-2.699375 5.800002,-7.071096l0,0.000547z" fill="'. $colour .'"/>
                </svg>';
        
        
        $plant = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="-8 -8 48 48" id="gel-icon-document">
            <path  fill="'. $colour .'" d="M27 9.067V0l-5.5 4.84L16 0l-5.5 4.84L5 0v9.067C5 17.012 9.347 21.327 14.874 22H15v6.592L2 21.056v4.204L13.664 32H17V21.996c5.583-.6 10-4.936 10-12.93z"/>
            <path  fill="'. $colour .'" d="M19.085 27.373v4.184L30 25.25v-4.188"/>
            </svg>';
        
        $herbarium_specimen =     '<svg xmlns="http://www.w3.org/2000/svg" viewBox="-8 -8 48 48" id="gel-icon-document">
            <path id="svg_1" d="m21.3,3l5.7,5.7l0,20.3l-22,0l0,-26l16.3,0m0.7,-3l-20,0l0,32l28,0l0,-24l-8,-8z" fill="'. $colour .'"/>
            <path stroke="null" id="svg_2" d="m21.500002,12.52935l0,-5.029353l-2.860001,2.684688l-2.860001,-2.684688l-2.860001,2.684688l-2.860001,-2.684688l0,5.029353c0,4.406994 2.260441,6.800471 5.134482,7.173776l0.06552,0l0,3.656501l-6.760002,-4.180126l0,2.331907l6.065282,3.738595l1.734721,0l0,-5.549096c2.903161,-0.332813 5.200002,-2.737938 5.200002,-7.172112l0,0.000555z" fill="'. $colour .'"/>
            <path stroke="null" id="svg_3" d="m17.085,23.116332l0,2.190671l6.415002,-3.302237l0,-2.192765" fill="'. $colour .'"/>
                </svg>';
                      

        switch ($icon_type){
            case 'Garden Plant':
                return $plant;
            case 'Garden Accession':
                return $accession;
            case 'Herbarium Specimen':
                return $herbarium_specimen;
            case 'location':
                return $map_pint;
            default:
                return $document;
        }


    }


?>