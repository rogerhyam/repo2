<?php
    
    $include_css[] = "/tools/tools.css";
    $include_scripts[] = "/tools/tools.js";
    
    require_once( '../inc/header.php' );
    require_once( 'tools_config.php' );

?>
<script src="tools.js" type="text/javascript" charset="utf-8"></script>
<div class="repo-doc-page">
    <h2>
        Repository Tools
        <pre style="display:none;">
            <?php echo print_r(@$_SESSION['repo-tools-permissions'], true) ?>
        </pre>
    </h2>
    
    <ul>
        <li>
            <a href="file_drop/index.php"><strong>File Drop:</strong></a>
            Upload JPG images, PDF documents and ZIP archive files to the repository. Do <strong>NOT</strong> use this for images of garden accessions/plants of specimens which can be linked to specific accession number or barcodes.
        </li>
        <li>
            <?php if(has_permission('item_images')){ ?>
                <a href="item_images/index.php"><strong>Item Images:</strong></a>
            <?php }else { ?>
                <strong>Item Images:</strong>
            <?php } ?>
            Upload images of existing repository item such as accessions, plants, herbarium specimens that can be linked to a specific accession number or barcode.
        </li>        
        <li>
            <?php if(has_permission('photo_all')){ ?>
                <a href="photo_all/index.php"><strong>Photo All:</strong></a>
            <?php }else { ?>
                <strong>Photo All:</strong>
            <?php } ?>
            Download lists of accessions/plants that need to be photographed.
        </li>
        <li>
            <?php if(has_permission('ftp_ingester')){ ?>
                <a href="ftp_ingester/index.php"><strong>FTP Ingester:</strong></a>
            <?php }else { ?>
                <strong>FTP Ingester:</strong>
            <?php } ?>
            Bulk upload of datasets via FTP.
        </li>
        <li>
            <?php if(has_permission('manage_cache')){ ?>
                <a href="manage_cache/index.php"><strong>Manage Cache:</strong></a>
            <?php }else { ?>
                <strong>Manage Cache:</strong>
            <?php } ?>
            The cache of resized images that are served in the interface and to 3rd parties externally.
        </li>
        <li>
            <?php if(has_permission('authorisation')){ ?>
                <a href="authorisation/index.php"><strong>Authorisation:</strong></a>
            <?php }else { ?>
                <strong>Authorisation:</strong>
            <?php } ?>
            Control who can access which tools.
        </li>

    </ul>
    
</div>


<div id="login-dialogue" title="Repo Tools Login" style="display: none;">
    
    <p>You must login before you use these tools.</p>

    <form method="POST" action="login.php" >
    <table>
        <tr>
            <th>Username:</th>
            <td><input type="text" id="username" name="username" /></td>
        </tr>
        <tr>
            <th>Password:</th>
            <td><input type="password" id="password" name="password" /></td>
        </tr>
        <tr>
            <th></th>
            <td style="text-align:right;"><button>Login</button></td>
        </tr>
    </table>
    </form>

    <p>Use your RBGE network credentials - username without dot plus current password.</p>
<?php if(isset($_GET['login_fail']) && $_GET['login_fail'] == 'true'){ ?>
    <p style="color: red;" >Login Failed: Did you type it right?</p>
<?php } // login fail?>

</div>

<?php
    require_once( '../inc/footer.php' );
?>
