<?php

    require_once( '../../../config.php' );
    require_once( '../tools_config.php' );
    require_once('upload_handler.php');
    
    $include_css[] = "/tools/item_images/item_images.css";
    $include_scripts[] = "/tools/item_images/item_images.js";
    require_once( '../../inc/header.php' );
?>
<div class="repo-doc-page">
     <div id="tabs" >
        	<ul>
        		<li><a href="#tabs-1">Add Images</a></li>
        		<li><a id="tabs-2-button" href="#tabs-2">Recently Added</a></li>
        	</ul>
        	<div id="tabs-1">
    	        
        	    <div id="formPanel">
    	            <form method="POST" action="index.php" enctype="multipart/form-data">
        	            <table id="lookupTable">
        	                <tr>
        	                    <td colspan="3">
        	                         <h1>RBGE Collection Linked Image Uploader</h1>
        	                    </td>
        	                </tr>
        	                <tr>
        	                    <th>Photographer:</th>
        	                    <td><input size="30" name="photographerInput" id="photographerInput" type="text" value="<?php
        	                     if (@$_REQUEST['photographerInput']){
        	                         echo $_REQUEST['photographerInput'];
        	                     }else{
        	                         echo $_SESSION['repo-tools-user-display-name'];
        	                     } 
    ?>" /></td>
        	                    <td>
        	                </tr>
          	                <tr>
            	                    <td colspan="3">
            	                        <h3>Linked Data</h3>
            	                    </td>
            	            </tr>  	                
        	                <tr>
        	                    <th>Barcode/Accession:</th>
        	                    <td><input size="30" name="accessionNumberInput" id="accessionNumberInput" type="text" value="<?php echo @$_REQUEST['accessionNumberInput'] ?>" /></td>
        	                    <td><button name="accessionNumberLookupButton" id="accessionNumberLookupButton" />Look Up</button>
        	                </tr>

        	                <tr>
        	                    <th>Identifier:</th>
        	                    <td id="identifierLabel" colspan="2" >-</td>
        	                </tr>
        	                <tr>
        	                    <th>Species Name:</th>
        	                    <td id="speciesNameLabel" colspan="2" >-</td>
        	                </tr>

        	                <tr>
        	                    <th>Family:</th>
        	                    <td id="familyLabel" colspan="2">-</td>
        	                </tr>
        	                <tr>
        	                    <th>Collector:</th>
        	                    <td id="collectorLabel" colspan="2">-</td>
        	                </tr>

        	                <tr>
        	                    <th>Coll. Date:</th>
        	                    <td id="collDateLabel" colspan="2">-</td>
        	                </tr>

        	                <tr>
        	                    <td colspan="3">
        	                        <h3>Add Photos</h3>
        	                    </td>
        	                </tr>

        	                 <tr>
            	                    <th>Select Files:</th>
            	                    <td><input  id="fileUploadField" type="file" multiple="true" name='files[]' accept="image/jpeg" /></td>
            	                    <td><button id="uploadButton" />Upload Files</button></td>
            	            </tr>
            	            <tr>
            	                    <th>&nbsp;</th>
            	                    <td colspan="2">Max. <?php echo get_max_file_upload_size() ?> combined.</td>
            	            </tr>
            	            <tr>
            	                    <td colspan="3">&nbsp;</td>
            	            </tr>
            	                <tr>
            	                    <td colspan="3">
            	                        <h3>Commit to Repository</h3>
            	                    </td>
            	                </tr>

        	                 <tr>
            	                    <th></th>
            	                    <td id="statusLabel"></td>
            	                    <td><button id="commitButton" />Commit</button></td>
            	                </tr>
        	            </table>
                    </form>
        	    </div>

        	    <div id="imageListPanel">

                    <?php
                        // work through the images in the directory
                        $images = array();
                        $user = @$_SESSION['repo-tools-username'];

                        if($user){
                            // check the directory exists
                            if(!file_exists("images/$user/")){
                                mkdir("images/$user/", 0777, true);
                            }

                            if ($dh = opendir("images/$user")) {

                                while (($file = readdir($dh)) !== false) {
                                    if(!preg_match('/^Photo_[a-z0-9]+\.jpg$/', $file) ) continue;

                                    $meta = file("images/$user/$file.txt");
                                    $originalFile = trim($meta[0]);
                                    $date = trim($meta[1]);
                                    if(!$date == '-'){
                                        $date = str_replace(':', '-', substr($date, 0, 10 ) );
                                    }
                                    
                                    $div_id = str_replace('.', '-', $file);
                                    echo "<div class=\"imagePreview\" id=\"$div_id\">";
                                    echo "<img src=\"images/index.php?image=$file\" /><br/>";

                                    echo "<div class=\"controls\">";
                                    echo "<span class=\"originalFile\">$originalFile</span><br/>";
                                    echo "Date: <input type=\"text\" class=\"dateField\" onchange=\"changeDate(this.value, '$file');\" readonly=\"readonly\" size=\"8\" value=\"$date\"/><br/>";
                                    echo "<button class=\"controlButton rotateLeftImageButton\" >Rotate-L</button>";
                                    echo "<button class=\"controlButton rotateRightImageButton\" >Rotate-R</button>";
                                    echo "<button class=\"controlButton deleteImageButton\"  >Remove</button>";
                                    echo "</div>";

                                    echo "</div>";

                              }
                                closedir($dh);
                            }
                        }

                    ?>
                </div>
                
            </div><!-- tabs-1 -->

            <!-- second tab - recent images -->
            <div id="tabs-2">
                <?php
                
                $my_images_uri = "/index.php?q=submitted_by_s:$user&sort=" . urlencode("indexed_at desc") . "&repo_type=complex&" . REPO_SOLR_QUERY_STRING;
                
                ?>
                
                <p>These are images that you have recently uploaded that are now queued for indexing. You can't change them while the indexer may be working on them. To view your uploaded images that <strong>have</strong> been indexed <a 
                    href="<?php echo $my_images_uri ?>">click here</a>.
                </p>
                <div id="recent-images">No images to display.</div>
                              
                
            </div>
    </div> <!-- all tabs -->
<?php
    require_once( '../../inc/footer.php' );
?>