<?php

    /*
        this is a big switch that works 
    
    */

    function augment(&$doc){
        
        /*
            add a country_name if there isn't one
        */
        if(
            (!isset($doc->country_name) || !$doc->country_name) // no name
            &&
            (isset($doc->country_iso) || isset($doc->geolocation)) // has country code and/or geolocation
          ){
              include_once('classes/CountryNameAugmenter.php');
              $cna = CountryNameAugmenter::getInstance();
              $cna->augment($doc);
          }
        
    }


?>