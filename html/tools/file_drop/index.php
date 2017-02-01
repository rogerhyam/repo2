<?php

    require_once( '../../../config.php' );
    require_once( '../tools_config.php' );
    
    $include_css[] = "/tools/file_drop/file_drop.css";
    $include_scripts[] = "/tools/file_drop/file_drop.js";
    $include_scripts[] = "https://maps.googleapis.com/maps/api/js?key=AIzaSyDFhrG5sFcydNTBtXJES8zkKtGaimp7k5c";
    require_once( '../../inc/header.php' );
    
    // check if they have a file uploaded to their cache
    
    // if they do load the metadata

?>
<div class="repo-doc-page">
    <div id="tabs" >
          	<ul>
          		<li><a href="#tabs-upload">Add File</a></li>
          		<li><a href="#tabs-recent" id="tabs-recent-button">Recently Added</a></li>
          	</ul>
          	
          	<div id="tabs-upload">

                <div id="upload-file">
                    
<?php
    // do we have an uploaded file?
    $user = $_SESSION['repo-tools-username'];
    $dir_path = "files/$user";
    if(file_exists("$dir_path/_meta.json")){
        
        $meta = json_decode(file_get_contents("$dir_path/_meta.json"));
        echo "<h3>". $meta->file_name . "</h3>";
        
        $thumb = "$dir_path/_thumb.jpg";
        if(file_exists($thumb)){
            $thumb .= "?id=" . uniqid(); // prevent browser caching
            echo "<img src=\"$thumb\" />";
        }
        
        // these are used as default values in the form - over the ones written in
        $title_from_file = $meta->title;
        $coordinates_from_file = @$meta->coordinates;
        $date_from_file = @$meta->date;
        $creator_from_file = @$meta->creator;
        $description_from_file = @$meta->description;
        
        
        if(@$_GET['duplicate']){
            echo '<p class="repo-warning"><strong>Warning:</strong> This looks like a duplicate of:<br/>' . $_GET['duplicate'] . '</p>';
        }
        
        echo '<p id="repo-file-present"><a href="clear_upload.php">Clear Uploaded</a></p>';
        
    }else{
        $meta = false;

        if(@$_GET['success']){
            echo "<p id=\"repo-file-absent\"><strong>{$_GET['success']}</strong> was submitted successfully.</p>";
        }else{
            echo "<p id=\"repo-file-absent\" >Your uploaded file will appear here.</p>";
        }
        
    }

?>
                    
                </div>                    
                    <div id="upload-form">
        	            <form method="POST" action="upload.php" id="repo-file-upload-form" enctype="multipart/form-data">
            	            <table id="upload-table">
        	                    <tr>
            	                    <td colspan="3">
            	                        <h3>1) Upload a File</h3>
            	                    </td>
            	                </tr>
            	                <tr>
                	                    <th>Select File:</th>
                	                    <td><input
                	                        id="repo-file-upload"
                	                        type="file"
                	                        name='file'
                	                        value="Upload File"
                	                        />
                	                    </td>
            	                        <td class="help-cell">
                	                        <a href="#" class="repo-context-help" >?</a>
                	                        <div class="repo-help-dialogue" title="File Upload">
                                              <p>
                                                  Start by picking a file to upload using this button. You can't edit the other fields until you have done this.
                                              </p>
                                              <p>
                                                  This tool will only accept images in JPG format and documents PDF format.
                                              </p>
                                              <p>
                                                  <strong>Hint:</strong> You can drag and drop a file directly onto this button to upload it. 
                                              </p>

                                            </div>
                	                    </td>
                	            </tr>
        	                </table>
                        </form>
                        
                        <form method="POST" action="commit.php">
            	            <table id="commit-table">
            	                
            	                <tr>
                	                    <td colspan="4">
                	                        <h3>2) Required Data</h3>
                	                    </td>
                	            </tr>
            	                
            	                <tr>
            	                    <th>Author:</th>
            	                    <td colspan="2" ><input
            	                        size="30"
            	                        name="creator"
            	                        id="creator"
            	                        class="repo-needs-file" 
            	                        type="text"
            	                        value="<?php
            	                     if(@$creator_from_file && strlen(trim($creator_from_file)) > 0 ){
     	                                echo $creator_from_file;
            	                     }elseif (@$_SESSION['repo-tools-file-drop']){
            	                         echo $_SESSION['repo-tools-file-drop']['creator'];
            	                     }else{
            	                         echo $_SESSION['repo-tools-user-display-name'];
            	                     } 
                                     ?>" 
                                        data-repo-regex="[A-Za-z]{3}"
                                     />
                                     </td>
            	                    <td class="help-cell">
             	                        <a href="#" class="repo-context-help" >?</a>
             	                        <div class="repo-help-dialogue" title="Title">
                                           <p>
                                               You MUST supply an author/creator for the file or photo. This will default to your name but can be changed.
                                           </p>
                                         </div>
             	                    </td>
            	                </tr>
            	                          
            	                <tr>
            	                    <th>Title:</th>
            	                    <td colspan="2" ><input
            	                        size="30"
            	                        name="title"
            	                        id="tile"
            	                        class="repo-needs-file"
            	                        type="text"
            	                        value="<?php 
            	                            if(@$title_from_file){
            	                                echo $title_from_file;
            	                            }else if (@$_SESSION['repo-tools-file-drop']){
            	                                echo $_SESSION['repo-tools-file-drop']['title'];
            	                            }  ?>"
            	                        data-repo-regex="[A-Za-z0-9]{3}"
            	                        />
            	                    </td>
            	                    <td class="help-cell">
            	                        <a href="#" class="repo-context-help" >?</a>
            	                        <div class="repo-help-dialogue" title="Title">
                                          <p>
                                              You MUST supply a title for the file or photo. This should be short but descriptive and may included the original file name.
                                          </p>
                                        </div>
            	                    </td>
            	                </tr>

                                <tr>
            	                    <th>Date:</th>
            	                    <td colspan="2" >
            	                        <input
            	                        size="30"
            	                        class="repo-date-field repo-needs-file"
            	                        name="date"
            	                        id="date"
            	                        type="text"
            	                        data-repo-regex="(^[0-9]{4}-[0-9]{2}-[0-9]{2}$)|(^[0-9]{4}$)"
            	                        value="<?php 
            	                          if(@$date_from_file){
             	                                echo $date_from_file;
             	                            }else if (@$_SESSION['repo-tools-file-drop']){
             	                                echo $_SESSION['repo-tools-file-drop']['date'];
             	                            }      
            	                         ?>"
            	                        />
            	                    </td>
        	                        <td class="help-cell">
            	                        <a href="#" class="repo-context-help" >?</a>
            	                        <div class="repo-help-dialogue" title="Date">
                                          <p>
                                              You MUST supply a date for the file or photo. This should be the published/created date or date the photo was taken.
                                          </p>
                                          <p>
                                              Use the dropdown or enter the date directly in the form yyyy-mm-dd (e.g. 1965-02-28). You may also enter just the year part of the date.
                                          </p>
                                        </div>
            	                    </td>
            	                </tr>
            	                
            	                <tr>
            	                    <th>Country:</th>
            	                    <td colspan="2" >
            	                       <select
            	                        name="country_iso"
            	                        id="country_iso"
            	                        class="repo-needs-file"
            	                        data-repo-regex="[A-Za-z]{2}"
            	                       >
            	                           <option value="">~ Not Set ~</option>
            	                           <?php
            	                           foreach(REPO_COUNTRIES_ISOS AS $code => $name){
            	                               
            	                               $selected = '';
            	                               if(@$_SESSION['repo-tools-file-drop'] && $_SESSION['repo-tools-file-drop']['country_iso'] == $code){
            	                                   $selected = 'selected';
            	                               }
            	                               
            	                               echo "<option value=\"$code\" $selected >$name ($code)</option>";
            	                           }
            	                           
            	                           ?>
            	                           
   
            	                       </select>
            	                    </td>
            	                    <td>
             	                        <a href="#" class="repo-context-help" >?</a>
             	                        <div class="repo-help-dialogue" title="Title">
                                           <p>
                                               You MUST supply a country for the file or photo. 
                                           </p>
                                           <p>
                                               This helps us to meet our responsibilities under the <a href="https://en.wikipedia.org/wiki/Nagoya_Protocol" target="new" >Nagoya Protocol</a>.
                                           </p>
                                           <p>
                                               This field is linked to the Coordinates field below. If coordinates are set then the country will automatically be selected here based on those coordinates and 
                                               you won't be able to change it.
                                           </p>
                                         </div>
             	                    </td>
            	                </tr>
            	                
            	                <tr>
            	                    <th>Copyright:</th>
            	                    <?php $copyright_statement = "In adding this content to the RBGE Digital Repository I am giving the RBGE permission to use it for any purpose, including but not limited to commercial purposes." ?>
            	                    <td colspan="2" id="copyright-cell" >
            	                       <select
            	                        name="copyright_s"
            	                        id="copyright_s"
            	                        class="repo-needs-file"
            	                        data-repo-regex="[A-Za-z]{2}"
            	                        value="<?php echo $copyright_statement?>"
            	                       >
            	                            <option value="">Disagree</option>
            	                            <option value="<?php echo $copyright_statement?>" <?php  if (@$_SESSION['repo-tools-file-drop']['copyright_s'] ==  $copyright_statement) echo 'selected'; ?> >Agree</option>
            	                       </select>
            	                       <?php echo $copyright_statement?>
            	                    </td>
            	                    <td>
             	                        <a href="#" class="repo-context-help" >?</a>
             	                        <div class="repo-help-dialogue" title="Title">
                                           <p>
                                               You MUST agree to the material you are adding to the repository being used by RBGE in the future including for commercial purposes.
                                           </p>
                                           <p>
                                               Without this agreement we can't make use of the contents of the repository in our work.
                                           </p>
                                         </div>
             	                    </td>
            	                </tr>
       	                        <tr>
                	                    <td colspan="4">
                	                        <h3>3) Additional Data</h3>
                	                    </td>
                	            </tr>
            	                <tr>
            	                    <th>Description:</th>
            	                    <td colspan="2" >
            	                        <textarea
            	                            size="30"
            	                            name="content"
            	                            id="content"
            	                            class="repo-needs-file"
            	                            type="text" ><?php  
            	                                if(@$description_from_file){
            	                                    echo $description_from_file;
            	                                }else{
            	                                    echo @$_SESSION['repo-tools-file-drop']['content'];
            	                                }
            	                                ?></textarea>
            	                    </td>
            	                    <td class="help-cell">
            	                        <a href="#" class="repo-context-help" >?</a>
            	                        <div class="repo-help-dialogue" title="Description field">
                                          <p>
                                              Use this field to add a description of the file.
                                          </p>
                                          <p>
                                              Adding a description will help people find the file when they search.
                                          </p>
                                        </div>
            	                    </td>
            	                </tr>
<?php if(@$meta->type == 'pdf'){ ?>
            	                <tr>
            	                    <th>Compliance&nbsp;Doc:</th>
            	                    <td colspan="2" class="checkbox-cell" ><input
            	                        size="30"
            	                        name="compliance_doc_b"
            	                        id="compliance_doc_b"
            	                        class="repo-needs-file"
            	                        type="checkbox"
            	                        value="1"
            	                        <?php  if (@$_SESSION['repo-tools-file-drop'] && @$_SESSION['repo-tools-file-drop']['compliance_doc_b'] ==  1) echo 'checked'; ?> />
            	                    </td>
            	                    <td class="help-cell" >
            	                        <a href="#" class="repo-context-help" >?</a>
            	                        <div class="repo-help-dialogue" title="Collector Code field">
                                          <p>
                                              Check this box if the document is concerned with compliance to some legal or moral process.
                                          </p>
                                          <p>
                                              Compliance documents may include memoranda of understanding, export permits, collecting permits etc.
                                          </p>
                                          <p>
                                               Typically you will also fill in a Collector Code to indicate which batch of specimens this document is associated with although that isn't required.
                                          </p>
                                        </div>
            	                    </td>
            	                </tr>
<?php } // end test for a document ?>
            	                <tr>
            	                    <th>Collector Code:</th>
            	                    <td colspan="2" ><input
            	                        size="30"
            	                        name="collector_id_s"
            	                        id="collector_id_s"
            	                        class="repo-autocomplete repo-needs-file"
            	                        data-repo-field="collector_id_s"
            	                        data-repo-case="upper"
            	                        type="text"
            	                        value="<?php  if (@$_SESSION['repo-tools-file-drop']) echo @$_SESSION['repo-tools-file-drop']['collector_id_s']; ?>" />
            	                    </td>
            	                    <td>
            	                        <a href="#" class="repo-context-help" >?</a>
            	                        <div class="repo-help-dialogue" title="Collector Code field">
                                          <p>
                                              Use this field to tag the file an official collector code from Coll Books.
                                          </p>
                                          <p>
                                              It is highly recommended you choose one from the drop down suggest list unless you are sure that the one you want to enter is correct.
                                          </p>
                                        </div>
            	                    </td>
            	                </tr>
            	                <?php
            	                
            	                    // Family
            	                    render_repeat_field_rows('family', 'Family', 'title', "(^[A-Z]{1}[a-z]+$)|(^$)", "
                                          <p>
                                              Use this field to tag the file with a family name.
                                          </p>
                                          <p>
                                              You can choose from the suggest list or enter a new one.
                                          </p>
                                           <p>
                                              This field can contain a single word starting with a capital.
                                           </p>
                                           <p>
                                              If you have more than one family to enter click the + button. 
                                          </p>");
                                          
                                    // Genus
                                    render_repeat_field_rows('genus', 'Genus', 'title', "(^[A-Z]{1}[a-z]+$)|(^$)", "
                                      <p>
                                          Use this field to tag the file with a genus name.
                                      </p>
                                      <p>
                                          You can choose from the suggest list or enter a new one.
                                          Suggestions are not restricted to the family entered above.
                                       </p>
                                       <p>
                                          This field can contain a single word starting with a capital.
                                       </p>
                                       <p>
                                          If you have more than one genus to enter click the + button. 
                                      </p>");
                                
                                    // Epithet
                                    render_repeat_field_rows('epithet', 'Epithet', 'lower', "(^[a-z]+$)|(^$)", "
                                        <p>
                                            Use this field to tag the file with a taxonomic epithet. This may be a species or subspecific epithet.
                                        </p>
                                        <p>
                                            You can choose from the suggest list or enter a new one.
                                            Suggestions are not restricted by the genus entered above.
                                        </p>
                                        <p>
                                           This field can contain a lowercase single word.
                                        </p>
                                         <p>
                                            If you have more than one epithet to enter click the + button. 
                                        </p>");
                                        
                                    // Keyword
                                    render_repeat_field_rows('keywords_ss', 'Keyword', 'Title',  "(^[A-Z0-9a-z ]+$)|(^$)", "
                                          <p>
                                              Use this field to tag the file with a keyword.
                                          </p>
                                          <p>
                                             This field can contain multiple words. The first should start with a capital letter.
                                          </p>
                                          <p>
                                              You can choose from the suggest list or enter a new one.
                                          </p>");
                                
                                ?>

            	                <tr>
            	                    <th>Coordinates:</th>
            	                    <td>
            	                        <input
            	                            size="30"
            	                            name="geolocation"
            	                            id="geolocation"
            	                            class="repo-needs-file"
            	                            type="text"
            	                            value="<?php  
            	                                if(@$coordinates_from_file){
            	                                    echo $coordinates_from_file;
            	                                }elseif(@$_SESSION['repo-tools-file-drop']['geolocation']){
            	                                    echo $_SESSION['repo-tools-file-drop']['geolocation'];
            	                                }
                                            ?>"
            	                            data-repo-regex="(^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$)|(^$)"
            	                        />
            	                        +/- <input
            	                                size="4"
            	                                name="geolocation_accuracy_i"
            	                                id="geolocation_accuracy_i"
            	                                class="repo-needs-file"
            	                                type="text"
            	                                value="<?php  
            	                                if(@$coordinates_from_file){
            	                                    
            	                                    if(strlen($coordinates_from_file) > 20){
            	                                        echo 5;
            	                                    }else{
            	                                        echo 10;
            	                                    }
            	                                    
            	                                    
            	                                }elseif(@$_SESSION['repo-tools-file-drop']['geolocation_accuracy_i']){
            	                                    echo $_SESSION['repo-tools-file-drop']['geolocation_accuracy_i'];
            	                                }
            	                                ?>"
            	                                data-repo-regex="^[0-9]{0,10}$"
            	                        />
            	                        metres
            	                    </td>
            	                    <td>
            	                        <a href="#" id="repo-map-dialogue-eye" class="repo-lookup-globe" >&#127760;</a>
            	                    </td>
            	                    <td class="help-cell">
            	                        <a href="#" class="repo-context-help" >?</a>
            	                        <div class="repo-help-dialogue" title="Coordinates">
                                          <p>
                                              Use this field to tag the file with a precise location.
                                          </p>
                                          <p>
                                              The values in this field must be the decimal latitude and longitude separated by a comma.
                                          </p>
                                          <p>
                                              Click the globe icon to pick the coordinates from a Google Map.
                                          </p>
                                          <p>
                                             This field is linked to the Country field above. If coordinates are set then the country will be based on those coordinates.
                                          </p>
                                        </div>
            	                    </td>
            	                </tr>
            	            
            	                <tr>
            	                    <td colspan="4">
            	                        <h3>4) Commit to Repository</h3>
            	                    </td>

            	                </tr>
            	                <tr>
            	                    <td colspan="4">
            	                        <p>Please check the details are correct. When you click "Commit" the file will be sent to the repository and only an administrator will be able to make changes.</p>
            	                    </td>

            	                </tr>
            	                 <tr>
                	                    <td colspan="4" class="commit-cell"><button id="repo-commit-button" class="repo-needs-file" />Commit</button></td>
                	                </tr>
            	            </table>
            	        </form>
            	    </div> <!-- form panel -->
            </div>
            
            <div id="tabs-recent">
                <h2>Recently Added</h2>
                 <?php

                    $my_images_uri = "/index.php?q=submitted_by_s:$user&sort=" . urlencode("indexed_at desc") . "&repo_type=complex&" . REPO_SOLR_QUERY_STRING;

                    ?>

                    <p>These are files that you have recently uploaded that are now queued for indexing. You can't change them while the indexer may be working on them. To view your uploaded files that <strong>have</strong> been indexed <a 
                        href="<?php echo $my_images_uri ?>">click here</a>.
                    </p>
                    
                 <ul id="recent-files">No images to display.</ul>
            </div>
            
    </div> <!-- end of tabs -->
    
    <div id="repo-map-dialogue" title="Coordinates Picker">
        <div id="map" style="height: 400px; width: 400px;"></div>
    </div>

</div> <!-- repo-doc-page --> 

<?php

function render_repeat_field_rows($field_name, $field_title, $field_case, $field_regex, $help_html){

    if (@$_SESSION['repo-tools-file-drop'][$field_name]){
      $values = $_SESSION['repo-tools-file-drop'][$field_name];
      $values = array_filter($values);
      if(count($values) == 0) $values[]= '';
    }else{
        $values = array('');
    }
    foreach($values as $value){
?>
<tr>
    <th><?php echo $field_title ?>:</th>
    <td><input
        size="30"
        name="<?php echo $field_name ?>[]"
        id="<?php echo $field_name ?>"
        class="repo-autocomplete repo-needs-file"
        data-repo-field="<?php echo $field_name ?>"
        data-repo-case="<?php echo $field_case ?>"
        data-repo-regex="<?php echo $field_regex ?>"
        type="text"
        value="<?php echo $value; ?>" /></td>
    <td class="button-cell">
        <button class="repo-field-add-button repo-needs-file" /><?php echo (count($values) > 1 && $values[0] != $value ? '-' : '+'); ?></button>
    </td>
    <td class="help-cell">
        <a href="#" class="repo-context-help" >?</a>
        <div class="repo-help-dialogue" title="<?php echo $field_title ?>"><?php echo $help_html ?></div>
    </td>
</tr>
<?php
    } // end foreach
    
} // end function


?>

<?php
    require_once( '../../inc/footer.php' );
?>