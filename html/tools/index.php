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
        <!--
        <li>
            <?php if(has_permission('file_drop')){ ?>
                <a href="file_drop/index.php"><strong>File Drop:</strong></a>
            <?php }else { ?>
                <strong>File Drop</strong>
            <?php } ?>
            Upload images and PDF documents to the repository. Do <strong>NOT</strong> use this for images of garden accessions/plants of specimens with barcodes.
        </li>
        -->
        <li>
            <?php if(has_permission('item_images')){ ?>
                <a href="item_images/index.php"><strong>Item Images:</strong></a>
            <?php }else { ?>
                <strong>Item Images:</strong>
            <?php } ?>
            Upload images of existing repository item such as accessions, plants, herbarium specimens.
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
