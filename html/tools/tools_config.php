<?php
    /* configuration for the tools directories - mainly authentication */
    
    
    // check if they are logged in or not
    if(!@$_SESSION['repo-tools-logged-in']){
    
        // NOT LOGGED IN
        
        // make sure they are on the tools index page where they can log in
        if(!preg_match('/\/tools\/index.php$/', $_SERVER['SCRIPT_NAME'])){
            header("Location: /tools/index.php");
            exit;
        }
        
        $show_login = 'true';
        
    }else{
        
        // LOGGED IN
        
         $show_login = 'false';
        
        // they are logged in but are they authorised to access this tool?
        // only needs check if they don't have admin permission
        if(!in_array('_admin_', $_SESSION['repo-tools-permissions'])){
            
            // are we in a named tool
            $matches = array();
            if(preg_match('/\/tools\/([a-zA-Z0-9_]+)\//', $_SERVER['SCRIPT_NAME'], $matches)){
                $current_tool = $matches[1];
                if(!in_array($current_tool, $_SESSION['repo-tools-permissions']) && $current_tool != 'file_drop'){
                    header('HTTP/1.0 403 Forbidden');
                    echo "Sorry: You don't have permission to access this tool.";
                    exit;
                }
            }
            
        }
        
    }
    
?>
<span id="repo-show-login-flag" data-repo-login-flag="<?php echo $show_login ?>">
