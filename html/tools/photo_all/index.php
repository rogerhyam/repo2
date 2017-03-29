<?php
  
    require_once( '../../../config.php' );
    require_once( '../tools_config.php' );
    require_once( '../../inc/functions.php' );
    
    require_once( '../../inc/header.php' );

?>
<div class="repo-doc-page" id="repo-tools-photo-all">
    
    <h2>Photo All</h2>
    
    <p>This is a simple utility to download a list of plants/accessions and the number of Accession Photos we have of them.</p>
    <p>There is a limit of 1,000 to the number of results returned.</p>
    
    <form action="get_list.php" method="GET">
<table>
    <tr>
        <th>Genus:</th>
        <td><input
            size="30"
            name="genus"
            id="genus"
            class="repo-autocomplete"
            data-repo-field="genus"
            data-repo-case="title"
            data-repo-regex="^[A-Za-z]{3}$"
            type="text"
            value="<?php echo @$_SESSION['repo-tools-photo-all-genus'] ?>" /></td>
    </tr>
    <tr>
        <th>Bed: </th>
        <td><input
            size="30"
            name="bed"
            id="bed"
            class="repo-autocomplete"
            data-repo-field="storage_location_bed_code_s"
            type="text"
            value="<?php echo @$_SESSION['repo-tools-photo-all-bed'] ?>" /></td>
    </tr>
    <tr>
      <td colspan="2" style="text-align: right"><input type="submit" value="Fetch CSV"></td>
    </tr>
</table>
    </form>
    
    <div style="color: red;">
        <?php echo @$_SESSION['repo-tools-photo-all-message'] ?>
    </div>
    
</div>