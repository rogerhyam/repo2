<?php
	require_once( '../../../config.php' );
	require_once( '../../inc/functions.php' );
    require_once( '../tools_config.php' );
    
	
	$include_css[] = "/tools/ftp_ingester/ftp_ingester.css";
	
    require_once( '../../inc/header.php' );
	
	$import_dir = '/home/ftpingest/item_images/'; // ending slash
	
?>  

<div class="repo-doc-page" id="repo-tools-ftp-ingester-item-images">
    <h2>FTP Ingester - Item Images</h2>
	<ul>
	
<?php


	if(@$_GET['token'] && isset($_SESSION['VALID_TOKEN']) && @$_GET['token'] == $_SESSION['VALID_TOKEN']){
		
		echo "<li>Importing Item Images</li>";
		echo "<li>Please be patient.</li>";
		
		import_dir($import_dir);
	
		echo "<li><a href=\"index.php\">Back to FTP Ingest.</a></li>";
		
		$_SESSION['VALID_TOKEN'] = null;
		
	}else{
		
		if(validate_dir($import_dir)){
		
			$token = uniqid();
			$_SESSION['VALID_TOKEN'] = $token;
			echo "<li class=\"repo-success\" ><strong>Validation Succeeded</strong>";
			echo"<li><a href=\"item_images.php?token=$token\">Start import</a></li>";
		}else{
			echo "<li class=\"repo-fail\" ><strong>Validation Failed</strong></li>";
		}
		
		echo"<li><a href=\"item_images.php\">Revalidate</a></li>";
		
	}
	
?>
	</ul>
</div>	
<?php
    require_once( '../../inc/footer.php' );
?>

<?php


function validate_dir($import_dir){

	echo "<li>Validating directory $import_dir</li>";
	
	
	// Directory exists
	if(file_exists($import_dir) && is_dir($import_dir)){
		echo "<li class=\"repo-success\" >Directory exits.</li>";
	}else{
		echo "<li class=\"repo-fail\" >Failed: Directory does not exits.</li>";
		return false;
	}
	
	// metadata file exists
	$metadata_file_path = $import_dir . 'meta.csv';
	
	if(file_exists($metadata_file_path) && !is_dir($metadata_file_path)){
		echo "<li class=\"repo-success\" >Metadata file $metadata_file_path exists.</li>";
	}else{
		echo "<li class=\"repo-fail\" >Failed: Metadata file $metadata_file_path does not exist.</li>";
		return false;
	}
	
	// read in the csv file
	$csv = fopen($metadata_file_path, "r");
	
	if(!$csv){
		echo "<li class=\"repo-fail\" >Failed: Couldn't open $metadata_file_path.</li>";
		return false;
	}
	
	// lose the header row
	$header = fgetcsv($csv);
	echo "<li>Ignoring line 1 as hearder row: ";
	echo implode(', ', $header);
	echo "</li>";
	
	$count = 2;	
	while (($line = fgetcsv($csv)) !== FALSE) {
		
		echo "<li class=\"repo-success\"  >Line: $count: ";
		echo implode(', ', $line);
		echo "</li>";
		
		// check the file exists.
		$file_path = $import_dir . $line[0];
		if(!file_exists($file_path) ){
			echo "<li class=\"repo-fail\" >Failed: $file_path does not exist.</li>";
			return false;
		}
		
		// check it is a jpeg
		if(exif_imagetype($file_path) != IMAGETYPE_JPEG){
			echo "<li class=\"repo-fail\" >Failed: $file_path is not a JPEG.</li>";
			return false;
		}
		
		// check the accession/barcode is OK
		$accession_barcode = $line[1];
		$query = "catalogue_number:\"$accession_barcode\"";
		$back = query_solr($query);
		if(!isset($back->response->numFound) || $back->response->numFound == 0){
			echo "<li class=\"repo-fail\" >Failed: $accession_barcode is not in the repository.</li>";
			return false;
		}
		
		// check there is a photographer
		$photographer = $line[2];
		if(!$photographer || strlen($photographer) < 5){
			echo "<li class=\"repo-fail\" >Failed: Photographer is missing or less than five characters.</li>";
			return false;
		}
		
		// check if there is something with the same md5 in there already
		$md5 = md5_file($file_path);
		$query = "md5_s:\"$md5\"";
		$result = query_solr($query);
        if($result->responseHeader->status == 0 && isset($result->response->docs) && count($result->response->docs) > 0){
            $title = $result->response->docs[0]->title[0];
            $uri = "/index.php?q=md5_s:$md5&repo_type=complex&" . REPO_SOLR_QUERY_STRING;
			echo "<li class=\"repo-fail\" >Failed: Looks like a duplicate of <a href=\"$uri\">$title</a></li>";
			return false;
        }
		
		
		$count++;
	}
	
	fclose($csv);
	
	return true;
	
}

function import_dir($import_dir){
	
	$metadata_file_path = $import_dir . 'meta.csv';
	
	$csv = fopen($metadata_file_path, "r");
	
	// bin the header row
	$header = fgetcsv($csv);
	
	// work through the rows
	while (($line = fgetcsv($csv)) !== FALSE) {
	
		// N.B. THIS CODE IS SIMILAR TO THAT IN THE ITEM_IMAGES UPLOADER TOOL.
		// FIX IT HERE CHECK IT WORKS THERE!
		
		$image_file_path = $import_dir . $line[0];
		$new_file_name = uniqid("Photo_") . '.jpg';
		
        // create a document
       $doc = array();

       // make a unique id for it - we all need one of these
       $doc['id'] = 'http://repo.rbge.org.uk/id/ui/' . $new_file_name;
       $doc['title'] = $line[0];

       // the other numbers are the ones we will use to link it to data later
       $doc['catalogue_number'] = $line[1]; // fixme 
       $doc['catalogue_number_other'][] = $line[0];
       $doc['catalogue_number_other'][] = $line[1];

	   // we get some data from the exif of the file - including the date
       $exif = @exif_read_data($image_file_path, 0, true);
       $exifArray = array();
       foreach ($exif as $key => $section) {
           foreach ($section as $name => $val) {
               $exifArray["$key.$name"] = $val;
           }
       }
	   
	   // look in a few places for the date
       if (array_key_exists('EXIF.DateTimeDigitized', $exifArray)){
           $date_str = $exifArray['EXIF.DateTimeDigitized'];
       }elseif(array_key_exists('EXIF.DateTimeOriginal', $exifArray)){
           $date_str = $exifArray['EXIF.DateTimeOriginal'];
       }elseif(array_key_exists('IFD0.DateTime', $exifArray)){
           $date_str = $exifArray['IFD0.DateTime'];
       }else{
           $date_str =  "-";
       }
       
       if($date_str && $date_str != '-'){
         $date = new DateTime($date_str);
         $doc['object_created'] = $date->format('Y-m-d\TH:i:s\Z');
         $doc['object_created_year'] = $date->format('Y');
       }
	     
       // finally dump the whole exif to the file
       // entire exif data as raw data field
       $doc['IGNORE_image_exif'] = json_encode($exif);

       // creation date if we have one
       $doc['creator'] = $line[2];

       // image details as new fields
       $size = getimagesize($image_file_path);
       $doc['image_width_pixels_i'] = $size[0];        
       $doc['image_height_pixels_i'] = $size[1];
       $doc['mime_type_s'] = $size['mime'];

       $doc['submitted_by_s'] = $_SESSION['repo-tools-username'];;
       $date = new DateTime();
       $doc['submitted_on_dt'] = $date->format('Y-m-d\TH:i:s\Z');



       // item_type depends on if it is an accession or specimen
       // we store them by type
       $repo_path = REPO_ROOT . '/item_images';
	   $barcodeAccession = trim($line[1]);
	   
       if(preg_match('/^E[0-9]{8}/i', $barcodeAccession)){
           
           // this is a photo of a herbarium specimen
           $doc['item_type'] = 'Specimen Photo';
           $path_parts = str_split($barcodeAccession, 3);
           $repo_path .= '/specimens/' . implode('/', $path_parts);

           // we definitely link this to the specimen it is of
           $doc['derived_from'] = 'http://data.rbge.org.uk/herb/' . $barcodeAccession;

       }else{

           // this is a photo of a garden accession or plant
           $doc['item_type'] = 'Accession Photo';
           $path_parts = str_split($barcodeAccession, 2);
           $repo_path .= '/accessions/' . implode('/', $path_parts);

           // link it to the plant/accession
           $doc['derived_from'] = 'http://data.rbge.org.uk/living/' . $barcodeAccession;

       }

       $doc['derivation_rank_i'] = 10;

       // make sure it exists
       @mkdir($repo_path, 0777, true);

	   // GOT TO HERE *********************************

       // work out the file names for photo and json
       $image_repo_path = $repo_path . '/'. basename($new_file_name);
       $json_repo_path =  $repo_path . '/'. basename($new_file_name, '.jpg') . '.json';

       // we record its relative position within the repository as the storage location
       $doc["storage_location"] = "Repository";
       $doc["storage_location_path"] = str_replace(REPO_ROOT, '', $image_repo_path);

       // write the json file there
       $out = array();
       $out[] = $doc;
	   if(!file_exists($json_repo_path)){   
		   file_put_contents($json_repo_path, JSON_encode($out, JSON_PRETTY_PRINT));
   	   }else{
		   echo "<p>Warning: JSON file already exists in repository. $json_repo_path</p>";
   	   }
	   /*
	   echo '<pre>';
	   print_r($doc);
	   echo '</pre>';
	   */
	   
       //echo $json_repo_path . "\n";

       // write the image file there but only if we don't already have it
       if(!file_exists($image_repo_path)){
           copy($image_file_path, $image_repo_path);
       }else{
		   echo "<p>Warning: Image file already exists in repository. $image_repo_path</p>";
   	   }
    
      // check everything is in place and clean up.
      if(file_exists($image_repo_path) && file_exists($json_repo_path)){
          unlink($image_file_path);
      }
      
      // queue it for indexing
      $data_location = str_replace(REPO_ROOT, '', $json_repo_path); // json path within repo
      require_once('../../../index/classes/IndexQueueMySQL.php');
      $queue = new IndexQueueMySQL('edited_items');
      $queue->enqueue($doc['id'], $data_location);
		
	  echo "<li>$line[0];</li>";
	  
	
	}
	
}

?>

	