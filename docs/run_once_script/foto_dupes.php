<?php

    require_once('../../config_bgbase_dump.php');
    
    $lines = file('foto_dupes.txt');
    $out = fopen('foto_black_list.txt', 'w');
    
    foreach($lines as $line){
        
        list($path, $code) = explode(':', $line);
        
        $matches = array();
        if(preg_match("/([^\\\]+\.jpg)$/", $path, $matches)){
            
            $filename = trim($matches[1]);
            
            $sql = "SELECT * FROM image_archive.uploaded_images as i WHERE i.accession_number = '$code' AND i.original_file_name = '$filename'";
            $result = $mysqli->query($sql);
            
            if($mysqli->error){
                echo $mysqli->error . "\n";
                break;
            }
            
            if($result->num_rows > 0){
                
                fwrite($out, "$filename\n");
                
                //echo "$filename\t$code\n";
                while($row = $result->fetch_assoc()){
                    //echo  "\t". $row['original_file_name'] . "\t" . $row['barcode_accession'] . "\n";
                }
            }else{
                echo "NOT Imported\t$filename\t$code\n";
            }
            
        }else{
            echo "WARNING: no file found for $path\n";
            continue;
        }
    }
    
    fclose($out);
    
    


?>