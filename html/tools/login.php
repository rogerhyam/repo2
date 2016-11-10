<?php

    require_once('../../config.php');
    
    $username = @$_REQUEST['username'];
    $password = @$_REQUEST['password'];

    $ldapconn = ldap_connect(ACTIVE_DIRECTORY_SERVER);
    if(!$ldapconn){
       echo "Problems connecting to ActiveDirectory";
       exit;
    }
   
    @$ldapbind = ldap_bind($ldapconn, "RBG-NT\\" . $username, $password);

    if ($ldapbind) {

       $dn = "OU=Staff,OU=Botanics,DC=rbge,DC=org,DC=uk";
       $filter= "sAMAccountName=" . $username;

       $justthese = array("ou", "name", "mail", "memberOf");

       $sr = ldap_search($ldapconn, $dn, $filter, $justthese );

       $info = ldap_get_entries($ldapconn, $sr);

       $_SESSION['repo-tools-logged-in'] = true;
       $_SESSION['repo-tools-user-display-name'] = $info[0]['name'][0]; 
       $_SESSION['repo-tools-username'] = $username;
       $_SESSION['repo-tools-permissions'] = array();
       
       // load the permissions from the db
       $db_path = '../../data_local/tools_permissions.db';
       $db = new SQLite3($db_path);
       
       // make sure it has a table in it
       $db->exec("CREATE TABLE IF NOT EXISTS tools_permissions (tool_name TEXT, user_name TEXT, created DATETIME DEFAULT CURRENT_TIMESTAMP );");
       
       // special case of no permissions set at all - so no ability to add anything
       $result = $db->query("SELECT count(*) as n FROM tools_permissions");
       $row = $result->fetchArray();
       if($row['n'] == 0){
           // no one has permission to do anything so add this user as an admin
           $stmt = $db->prepare("INSERT INTO tools_permissions (user_name, tool_name) VALUES (:user, :tool)");
           $stmt->bindValue(':user', $username, SQLITE3_TEXT);
           $stmt->bindValue(':tool', '_admin_', SQLITE3_TEXT);
           $stmt->execute();
       }
       
       $stmt = $db->prepare("SELECT tool_name FROM tools_permissions WHERE user_name = :user ORDER BY user_name");
       $stmt->bindValue(':user', $username, SQLITE3_TEXT);
       $result = $stmt->execute();
       while($row = $result->fetchArray()){
           $_SESSION['repo-tools-permissions'][] = $row['tool_name'];
       }
       
       header('Location: index.php');

    } else {

       $_SESSION['repo-tools-logged-in'] = false;
       $_SESSION['repo-tools-user-display-name'] = null; 
       $_SESSION['repo-tools-username'] = null; 
   
       header('Location: index.php?login_fail=true');

    }

?>
<pre>
    <?php print_r($_SESSION); ?>
</pre>