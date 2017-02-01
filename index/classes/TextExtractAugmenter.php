<?php

include_once('classes/BaseAugmenter.php');

/*
    This extracts text from files an populates the extracted_text_s field.

*/

class TextExtractAugmenter extends BaseAugmenter
{


    public function augment($doc){
     
     // check the mimetype - only pdf for now
     if($doc->mime_type_s != 'application/pdf'){
        echo "TextExtractAugmenter: Unsupported mime type '{$doc->mime_type_s}'. Giving up.\n";
        return;
     }
     
     // check we have an attached file
     if(!isset($doc->storage_location_path)){
         echo "TextExtractAugmenter: No attached file - quitting.\n";
         return;
     }
     
     $full_path = REPO_ROOT . $doc->storage_location_path;
     if(!file_exists($full_path) || is_dir($full_path)){
         echo "TextExtractAugmenter: Can't find file at $full_path - quitting\n";
         return;
     }
     
     
     // call solr to extract the text

     
     echo "\tTextExtractAugmenter: Extracting from $full_path \n";
     
     $uri = REPO_SOLR_URI . '/update/extract?extractOnly=true&wt=json&indent=true';
     $args['file'] = new CurlFile($full_path, $doc->mime_type_s);
          
     $ch = curl_init( $uri );
     curl_setopt($ch, CURLOPT_POST,1);
     curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
     curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
     
     // Send request.
     $result = curl_exec($ch);
     curl_close($ch);
 
     $result = json_decode($result);
     
     // was it successful
     if($result->responseHeader->status != 0 || !isset($result->$full_path)){
         echo "TextExtractAugmenter: Failed to extract text - quitting\n";
         return;
     }
     
     $raw_text = htmlspecialchars_decode(strip_tags($result->$full_path));
     $raw_text = preg_replace('/^\h*\v+/m', '', $raw_text); // excessive blank lines
     
     $doc->extracted_txt = $raw_text;

     $count = strlen($raw_text);
     
     echo "\tTextExtractAugmenter: Extracted $count characters;\n";

     //  print_r($result);
     
     // 
     
     // curl "http://localhost:8983/solr/gettingstarted/update/extract?&extractOnly=true&wt=json&indent=true" -F "myfile=@stories.pdf"
     
     
        
    }

}

?>