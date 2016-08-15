<?php

    /*
        rename the pdf output of ABBYY into a file structure suitable for the repository
        
        Notes from the Royal Botanic Garden Edinburgh. Volume ** No. ** (year)

    */

    $input_dir = '/media/ocr/Output Folder/';
    $output_dir = '/media/ocr/temp/Notes_from_RBGE/';
    
    // create the initial json file
    $meta = new stdClass();
    $meta->title = "Notes from the Royal Botanic Garden Edinburgh";
    $meta->id = "http://repo.rbge.org.uk/id/documents/publications/Notes_from_RBGE";
    $meta->item_type = "Directory";
    $meta->data_location = "/documents/publications/Notes_from_RBGE.json";
    $meta->storage_location = "Repository";
    $meta->storage_location_path = "/documents/publications/Notes_from_RBGE";
    file_put_contents("/media/ocr/temp/Notes_from_RBGE.json", json_encode(array($meta)));
    
    // work through the files in the input folder
    $vols = scandir($input_dir);
    foreach($vols as $vol){
        
        $matches = array();
        if(!preg_match('/^NRBGE_([0-9]{4})_([0-9]{4})_(.+)\.pdf$/', $vol, $matches)) continue;
        
        //print_r($matches);
        
        $vol_number = $matches[1] +0;
        $year = $matches[2] + 0;
        $part = ltrim($matches[3], '0');
        
        $title = "Notes from the Royal Botanic Garden Edinburgh. Volume $vol_number No. $part ($year)";
        echo "$title\n";
        
        $base_name = str_replace('.', '', $title);
        $base_name = str_replace(' ', '_', $base_name);
        
        $pdf_name = $base_name . ".pdf";
        $json_name = $base_name . ".json";
        
        $dir = get_volume_dir($vol_number);
        
        // create a json out file
        $meta = new stdClass();
        $meta->title = $title;
        $meta->id = "http://repo.rbge.org.uk/id/documents/publications/Notes_from_RBGE/$vol_number/$pdf_name";
        $meta->item_type = "Document";
        $meta->data_location = "/documents/publications/Notes_from_RBGE/$vol_number/$json_name";
        $meta->summary_image_s = "/documents/publications/Notes_from_RBGE/$vol_number/$json_name.jpg";
        $meta->storage_location = "Repository";
        $meta->storage_location_path = "/documents/publications/Notes_from_RBGE/$vol_number/$pdf_name";
        $meta->derived_from = "http://repo.rbge.org.uk/id/documents/publications/Notes_from_RBGE/$vol_number";
        $meta->mime_type_s = "application/pdf";
 
        $meta->object_created_year = $year;
        file_put_contents($dir . $json_name, json_encode(array($meta)));
        
        // create a summary image
        $summary_image_path = $dir . $json_name . '.jpg';
        $im = new Imagick();
        $im->setResolution(300,300);
        $im->readimage($input_dir . '/' . $vol . '[0]'); 
        $im->setImageOpacity(1);
        $im->resizeImage(1000,1000,Imagick::FILTER_LANCZOS,1, true);
        $im->setImageFormat('jpeg');
        $im->writeImage($summary_image_path); 
        $im->clear(); 
        $im->destroy();
        
        // actually move the file
        copy($input_dir . '/' . $vol, $dir . $pdf_name);
        
        echo "done $dir" . "$pdf_name\n";
        
    
    }
    
    
    function get_volume_dir($vol_number){
        
        global $output_dir;
        
        $dir = $output_dir . $vol_number;
        
        if(!file_exists($dir)){
            
            // mkdir
            @mkdir($dir, 0777, true);
            
            // mk json for dir
            $meta = new stdClass();
            $meta->title = "Notes from the Royal Botanic Garden Edinburgh. Volume $vol_number";
            $meta->id = "http://repo.rbge.org.uk/id/documents/publications/Notes_from_RBGE/$vol_number";
            $meta->item_type = "Directory";
            $meta->data_location = "/documents/publications/Notes_from_RBGE/$vol_number.json";
            $meta->storage_location = "Repository";
            $meta->storage_location_path = "/documents/publications/Notes_from_RBGE/$vol_number";
            $meta->derived_from = "http://repo.rbge.org.uk/id/documents/publications/Notes_from_RBGE";
            file_put_contents("$dir.json",json_encode(array($meta)));
        
        }

        return $dir . '/';
        
    }
    
    // ignore those not starting with NRBGE
    
    // extract the parts of the name
    
    // create a folder based on years
    
    // create a filename based on title
    
    // create a .json file with correct content (year and title)
    
    // copy the file
    
    
    


?>