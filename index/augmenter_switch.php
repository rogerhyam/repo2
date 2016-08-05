<?php

    /*
        this is a big switch that works 
    
    */

    function augment($doc){
        
        // firstly trigger the derived fields to be completed
        // will also trigger reindexing of any items derived from this one
        include_once('classes/DerivedItemAugmenter.php');
        $dia = new DerivedItemAugmenter();
        $dia->augment($doc);
        
        //  add a country_name if there isn't one
        if(
            (!isset($doc->country_name) || !$doc->country_name) // no name
            &&
            (isset($doc->country_iso) || isset($doc->geolocation)) // has country code and/or geolocation
        ){
              include_once('classes/CountryNameAugmenter.php');
              $cna = new CountryNameAugmenter();
              $cna->augment($doc);
        }
        
        
        // extract text from attached files based on mime-type
        if($doc->mime_type_s == 'application/pdf'){
            include_once('classes/TextExtractAugmenter.php');
            $tea = new TextExtractAugmenter();
            $tea->augment($doc);
        }
        
          
        
    }


?>