<?php
  
    require_once( '../../../config.php' );
    require_once( '../tools_config.php' );
    require_once( '../../inc/functions.php' );
    
    
    $include_css[] = "/tools/spot_edit/spot_edit.css";
    $include_scripts[] = "/tools/spot_edit/spot_edit.js";
    require_once( '../../inc/header.php' );
    


?>
<div class="repo-doc-page" id="repo-tools-spot-edit">
    <h2>Spot Edit</h2>
    <p>This enables the editing of metadata of single items as stored in the repository - with some reservations. Be aware that some items will be overwritten if they are re-ingested into the repository as part of a batch process. Also some individual fields will be overwritten at index time if this is a derived item or if it has annotations associated with it.</p>

<?php

  $data_path = REPO_ROOT .  base64_decode($_GET['data_location']);
  
  $too_big = false;  
  $size = filesize($data_path);
  if($size > 1000000){
     $too_big = true;
  }else{ 
     $json = json_decode(file_get_contents($data_path));
     if(count($json) > 1) $too_big = true;
  }
  
  // single item test
  if($too_big){
      echo '<strong>Sorry: This tool can only be used for editing single item data files like those for publications and photos.</strong>';
  }else{
      $doc = $json[0];
  
?>    
<div id="tabs">
    
    <ul>
      <li><a href="#tabs-form">Form</a></li>
      <li><a href="#tabs-metadata">Raw Metadata</a></li>
      <li><a href="#tabs-index">Raw Index Value</a></li>
    </ul>
    
    <div id="tabs-form">
        <form action="update.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $doc->id ?>" />
            <input type="hidden" name="data_path" value="<?php echo $data_path ?>" />
            <input type="hidden" name="data_location" value="<?php echo $_GET['data_location'] ?>" />
            <table>
                <tr>
                    <th>ID:</th>
                    <td><?php echo $doc->id ?></td>
                </tr>
                <tr>
                    <th>Title:</th>
                    <td>
                        <input type="text" class="repo-text-field" value="<?php echo @$doc->title ?>" name="title" />
                    </td>
                </tr>

                <tr>
                    <th>Derived From:</th>
                    <td>
                        <input type="text" value="<?php echo @$doc->derived_from ?>" name="derived_from" class="repo-text-field" />
                    </td>
                </tr>
                
                <tr>
                    <th>Catalogue Number:</th>
                    <td>
                        <?php render_multivalue_field('catalogue_number', $doc); ?>
                    </td>
                </tr>
        
                <tr>
                    <th>Other Catalogue Numbers:</th>
                    <td>
                        <?php render_multivalue_field('catalogue_number_other', $doc); ?>
                    </td>
                </tr>
        
                <tr>
                    <th>Creator:</th>
                    <td>
                        <?php render_multivalue_field('creator', $doc); ?>
                    </td>
                </tr>
        
                <tr>
                    <th>Content:</th>
                    <td>
                        <textarea name="content"><?php echo @$doc->content ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th>Key Words:</th>
                    <td>
                        <?php render_multivalue_field('keywords_ss', $doc); ?>
                    </td>
                </tr>
                <tr>
                    <th>Family:</th>
                    <td>
                        <?php render_multivalue_field('family', $doc); ?>
                    </td>
                </tr>
                <tr>
                    <th>Genus:</th>
                    <td>
                        <?php render_multivalue_field('genus', $doc); ?>
                    </td>
                </tr>
        
                <tr>
                    <th>Epithet:</th>
                    <td>
                        <?php render_multivalue_field('epithet', $doc); ?>
                    </td>
                </tr>
        
                <tr>
                    <th>Country Name:</th>
                    <td>
                        <?php render_multivalue_field('country_name', $doc); ?>
                    </td>
                </tr>
                <tr>
                    <th>Country Code:</th>
                    <td>
                        <?php render_multivalue_field('country_iso', $doc); ?>
                    </td>
                </tr>
        
                <tr>
                    <th>Show in results:</th>
                    <td>
                        <?php
                            if(isset($doc->hidden_b) && $doc->hidden_b) $hidden = true;
                            else $hidden = false;
                        ?>
                        <input type="radio" value="1" name="hidden_b" <?php echo ($hidden ? 'checked' : ''); ?> /> Hide
                        <input type="radio" value="0" name="hidden_b" <?php echo ($hidden ? '' : 'checked'); ?> /> Display
                    </td>
                </tr>
        
                <tr>
                    <td colspan="2" style="text-align:center;">
                        <button type="button" onclick="window.location.href = '/index.php?q=<?php echo urlencode('id:"'. $doc->id .'"' ) . '&repo_type=hidden&' . REPO_SOLR_QUERY_STRING ?>'">View</button>
                        <button type="submit" >Save</button>
                    </td>
                </tr>

            </table>
        </form>
    </div><!-- form tab -->
    
    <!-- Metadata -->
    <div id="tabs-metadata">
        <div class="spot-edit-raw">
            <pre>
                <?php echo  print_r($doc);?>
            </pre>
        </div>
    </div>
    
    
    <!-- Index value -->
    <div id="tabs-index">
          <div class="spot-edit-raw">
            <pre>
<?php 
echo  print_r(get_solr_item_by_id($doc->id));
?>
            </pre>
          </div>
    </div>
    
</div><!-- tabs -->

</div>

<?php

} // end single item test

require_once( '../../inc/footer.php' );

function render_txt_field($field_name, $doc){
    echo '<input type="text" class="repo-text-field" value="'. @$doc->$field_name .'" name="'. $field_name .'" />';
}

function render_multivalue_field($field_name, $doc){
    
    $values = array();
    
    if(isset($doc->$field_name)){
        
        // there are cases where this has been an object - ingester error?
        if(is_object($doc->$field_name)){
            $doc->$field_name = get_object_vars($doc->$field_name);
        }
        
        if(is_array($doc->$field_name)){
            $values = $doc->$field_name;
        }else{
            $values[] = $doc->$field_name;
        }
        
    }
    
    foreach($values as $val){
        echo '<input type="text" class="repo-text-field" name="'. $field_name .'[]" value="' .  htmlentities($val) . '" /><br/>';
    }

    // blank one on the end
    echo '<input type="text" placeholder="~ Add Item ~" class="repo-text-field" name="'. $field_name .'[]" value="" /><br/>';

}

?>