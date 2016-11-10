<?php
    require_once( '../../../config.php' );
    require_once( '../tools_config.php' );
    require_once( '../../inc/header.php' );
?>
<div class="repo-doc-page">
    <h2>Tools Authorisation</h2>
    <p>Use this utility to control permissions of who can access which tools. Access to the _admin_ tool gives access to all tools.</p>

<?php

    // we have permissions stored in a little sqlite db
    
    $db_path = '../../../data_local/tools_permissions.db';
    $db = new SQLite3($db_path);
    // make sure it has a table in it
    $db->exec("CREATE TABLE IF NOT EXISTS tools_permissions (tool_name TEXT, user_name TEXT, created DATETIME DEFAULT CURRENT_TIMESTAMP );");

    display_users('_admin_', $db);

    $files = scandir('../');
    foreach($files as $file){
        if(preg_match('/^\./', $file)) continue;
        if(!is_dir('../' . $file)) continue;
        display_users($file, $db);
    }


function display_users($permission, $db){
    echo "<h2>";
    echo $permission;
    echo "</h2>";

    // fetch the users with this permission
    $stmt = $db->prepare("SELECT * FROM tools_permissions WHERE tool_name = :tool ORDER BY user_name");
    $stmt->bindValue(':tool', $permission, SQLITE3_TEXT);
    $result = $stmt->execute();

    $count = 0;
    while($row = $result->fetchArray()){
        
        if($count == 0){
            echo "<p><em>Click name to remove:</em> ";
        }else{
            echo ", ";
        }
        
        echo '<a href="change_permission.php?add=false&tool_name=' . $permission . '&user_name=' . $row['user_name'] . '">';
        echo $row['user_name'];
        echo '</a>';
        
        $count ++;

    }
    if($count > 0){
        echo ".</p>";
    }
    

?>
    
    <form action="change_permission.php" method="GET">
        <p><em>Give user access to tool:</em>
        <input type="hidden" name="add" value="true"/>
        <input type="hidden" name="tool_name" value="<?php echo $permission ?>"/>
        <input type="text" name="user_name" placeholder="User Name" value=""/>
        <input type="submit" value="Add" />
        </p>
    </form>



<?php
  
    
}

?>
    
    
</div>

<?php
    require_once( '../../inc/footer.php' );
?>