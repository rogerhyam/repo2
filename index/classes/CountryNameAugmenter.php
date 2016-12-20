<?php

include_once('classes/BaseAugmenter.php');

class CountryNameAugmenter extends BaseAugmenter
{
    
    var $countries = REPO_COUNTRIES_ISOS;
    
    public function augment($doc){
        
        // make sure the country name is already an array of names
        if(isset($doc->country_name) && !is_array($doc->country_name)){
            $doc->country_name = array($doc->country_name);
        }else{
            $doc->country_name = array();
        }
        
        // what is the iso field?
        if(isset($doc->country_iso)){
            
            if(is_array($doc->country_iso)){
                $codes = $doc->country_iso;
            }else{
                $codes = array();
                $codes[] = $doc->country_iso;
            }
            
            foreach($codes as $code){
                
                // if we have a name for that code and it isn't already there add it
                if(array_key_exists($code, $this->countries)){
                    $name = $this->countries[$code];
                    if(!in_array($name, $doc->country_name)){
                        $doc->country_name[] = $name;
                    }
                }
                
                
            }
            
        }
        
    }

}

?>