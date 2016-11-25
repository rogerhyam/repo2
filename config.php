<?php

    session_start();

    date_default_timezone_set('UTC');
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    $live = false;
    
    // switch to tell if we are live or not
    if($live){
        $solr_core = 'rbge01';
        $repo_root_path = '/media/repo_disk';
    }else{
        $solr_core = 'gettingstarted';
        $repo_root_path = '/var/www/data';
    }
    
    define('ACTIVE_DIRECTORY_SERVER', "192.168.150.90");
    define('INDEX_QUEUE_PATH', '/var/www/index/queues' );
    
    // full path to documents directory
    define('REPO_ROOT', $repo_root_path);
    
    // URI for solr server collection
    define('REPO_SOLR_HOST', "localhost");
    define('REPO_SOLR_PORT', "8983");
    define('REPO_SOLR_PATH', "/solr/" . $solr_core);
    define('REPO_SOLR_URI', "http://". REPO_SOLR_HOST . ":". REPO_SOLR_PORT . REPO_SOLR_PATH);
    define('REPO_SOLR_PAGE_SIZE', 10);
    
    // default bits to include in a query string to get facetting to work.
    define('REPO_SOLR_QUERY_STRING',  'facet=true&facet.mincount=1&facet.limit=100&facet.field=genus&facet.field=family&facet.field=epithet&facet.field=country_name&facet.field=item_type&facet.field=object_created_year&start=0&rows=' . REPO_SOLR_PAGE_SIZE ); 
    
    // API KEYS
    define('REPO_GOOGLE_API_KEY', 'AIzaSyCuEKY1-ZvWGBfD_DFYe-kxAHop0hJPuYE');
    
    // A function that could be used anywhere
    function get_identifier_for_repo_file($repo_path, $qualifier = false){
        
        // we need to be sure none of the parts of the file path contain unfriendly url chars
        $id = str_replace(' ', '_', $repo_path);
        $parts = explode('/', $id);
        for ($i=0; $i < count($parts); $i++) { 
            $parts[$i] = urlencode($parts[$i]);
        }
        $id = implode('/', $parts);
        if($qualifier){
            $id .= '#' . urlencode($qualifier);
        }
        
        return 'http://repo.rbge.org.uk/id' . $id;
        
    }
    
    /*
        useful for on/off permissions in front end
    */
    function has_permission($tool_name){
        if(isset($_SESSION['repo-tools-permissions']) && in_array('_admin_', $_SESSION['repo-tools-permissions'])) return true;
        if(isset($_SESSION['repo-tools-permissions']) && in_array($tool_name, $_SESSION['repo-tools-permissions'])) return true;
        return false;
    }
    
    // really commonly needed function
    function get_field_value_as_string($doc, $field_name, $separator = ' | '){
        if(isset($doc->$field_name)){
            if(is_array($doc->$field_name)){
                return implode($separator, $doc->$field_name);
            }else{
                return $doc->$field_name;
            }
        }else{
            return '';
        }
    }
    
    
    function get_max_file_upload_size() {
        //select maximum upload size
        $max_upload = ini_get('upload_max_filesize');
        //select post limit
        $max_post = ini_get('post_max_size');
        //select memory limit
        $memory_limit = ini_get('memory_limit');
        // return the smallest of them, this defines the real limit
        return min($max_upload, $max_post, $memory_limit);
    }
    
    
    
?>