<?php

    date_default_timezone_set('UTC');
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // full path to documents directory
    define('REPO_ROOT', "/var/www/data");
    
    // URI for solr server collection
    define('REPO_SOLR_HOST', "localhost");
    define('REPO_SOLR_PORT', "8983");
    define('REPO_SOLR_PATH', "/solr/gettingstarted");
    define('REPO_SOLR_URI', "http://". REPO_SOLR_HOST . ":". REPO_SOLR_PORT . REPO_SOLR_PATH);
    define('REPO_SOLR_PAGE_SIZE', 10);
    
    // default bits to include in a query string to get facetting to work.
    define('REPO_SOLR_QUERY_STRING',  'facet=true&facet.mincount=1&facet.limit=100&facet.field=genus&facet.field=family&facet.field=epithet&facet.field=country_name&facet.field=item_type&facet.field=object_created_year&start=0&rows=' . REPO_SOLR_PAGE_SIZE ); 
    
    
?>