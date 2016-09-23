<?php


    /*
        script to turn page tiffs into multipage tifs that the 
        
    */

    $out_path = '/media/ocr/Input Folder/';

    
    // scan volumes
    $vols = scandir('/media/bhl/Vegetation_of_Caithness');
    foreach($vols as $vol){
        
        $matches = array();
        if(!preg_match('/^VoC_([0-9]{4})/', $vol, $matches)) continue;       
        
        // do 14 onwards
        // if($vol != 'NRBGE_FoB_0003') continue;
         
        $parts = scandir('/media/bhl/Vegetation_of_Caithness/'. $vol);
        foreach($parts as $part){
            if(!preg_match('/^VoC_/', $part)) continue;
            process_dir('/media/bhl/Vegetation_of_Caithness/'. $vol . '/' . $part, $out_path . $part . '.tif');
        }
    }

    function process_dir($in_path, $out_path){
    
        echo "Processing: $in_path\n";
    
        $multi_tif = new Imagick();
        
        $files = scandir($in_path);

        foreach( $files as $f )
        {
            // ignore non-tifs
            if(!preg_match('/\.tif$/', $f)) continue;
            echo "\tAdding: $f \n";

            $page = new Imagick();
            $page->readImage($in_path . '/' . $f);
            $page->resizeImage(1250,1250,Imagick::FILTER_LANCZOS,1, true);
            $multi_tif->addImage($page);
            $page->clear();

        }

     
        echo "\tStarting write $out_path\n";
        $multi_tif->setImageCompression(Imagick::COMPRESSION_LZW);
        $multi_tif->writeImages($out_path, true);
        $multi_tif->clear();
        echo "\tStarting writing $out_path\n";
        
        
    }


?>