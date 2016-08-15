<?php


    /*
        script to turn page tiffs into multipage tifs that the 
        
    */

    $in_path = '/media/bhl/NRBGE_0001/NRBGE_0001_1900_0001/';
    $out_path = '/media/ocr/Input Folder/';

    
    // scan volumes
    $vols = scandir('/media/bhl/');
    foreach($vols as $vol){
        
        $matches = array();
        if(!preg_match('/^NRBGE_([0-9]{4})/', $vol, $matches)) continue;       
        
        // do 14 onwards
        if($matches[1] < 14) continue;
         
        $parts = scandir('/media/bhl/'. $vol);
        foreach($parts as $part){
            if(!preg_match('/^NRBGE_/', $part)) continue;
            process_dir('/media/bhl/'. $vol . '/' . $part, $out_path . $part . '.tif');
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
            $page->resizeImage(1500,1500,Imagick::FILTER_LANCZOS,1, true);
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