<?php 
    require_once('inc/header.php');
    
//    echo $_GET['fq'];
//    exit;
    
    // before we do anything we redirect if we haven't got a q
    // we always need to run a query so we have the facets available.
    if (!@$_GET['q']) {
         header('Location: index.php?q=_text_:*&repo_type=hidden&' . REPO_SOLR_QUERY_STRING);
    }

?>

     <form class="repo-search-form" method="GET" action="index.php">
<div id="repo-page-content">
   
        <input type="text" name="q" id="repo-input-q" value="<?php echo @$_GET['repo_type'] != 'hidden' ? htmlspecialchars(@$_GET['q']) : ''; ?>"/>        
        <input type="hidden" name="repo_type" id="repo-input-repo-type" value="simple"/>
        <input type="hidden" name="start" id="repo-input-start" value="<?php echo @$_GET['start'] ? $_GET['start'] : 0;  ?>" />
        <input type="hidden" name="rows" value="<?php echo REPO_SOLR_PAGE_SIZE ?>" />
        <input type="hidden" name="facet" value="true" />
        <input type="hidden" name="facet.sort" value="count" />
        <input type="hidden" name="facet.mincount" value="1" />
        <input type="hidden" name="facet.limit" value="100" />
        <input type="hidden" name="facet.field" value="genus" />
        <input type="hidden" name="facet.field" value="family" />
        <input type="hidden" name="facet.field" value="epithet" />
        <input type="hidden" name="facet.field" value="country_name" />
        <input type="hidden" name="facet.field" value="item_type" />
        <input type="hidden" name="facet.field" value="object_created_year" />
        
        <input type="submit" id="repo-input-submit" value="Search"/>
    
    
<?php 

        // is it a simple query or is there a field name in it?      
        $query_string =  $_SERVER['QUERY_STRING'];
        
        // remove the repo_type param so it doesn't go to solr
        $query_string = preg_replace('/&repo_type=[a-z_]+/', '', $query_string);
        
        // if we are doing a simple query then we should escape : in the string
        // because that implies a field name
        if(@$_GET['repo_type'] == 'simple'){
            $current_q = $_GET['q'];
            $new_q = str_replace('\\', '\\\\', $current_q);
            $new_q = str_replace(':', '\:', $new_q);
            $query_string = str_replace('q='. urlencode($current_q) . '&', 'q=' . urlencode($new_q) . '&', $query_string);
        }
            
        // call solr
        $uri = REPO_SOLR_URI . '/query?'. $query_string;
        $ch = curl_init($uri);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );        
        $result = json_decode(curl_exec($ch));
        curl_close($ch);
              
        echo "<p>Showing {$result->response->start} to ". (count($result->response->docs) + $result->response->start) ." of {$result->response->numFound} results found.</p>";
        
        $row_count = 0;
        foreach($result->response->docs as $doc){
             write_doc($doc, $row_count);
            $row_count++;
        }
        
        // flag it up if there is only one search result
        if($result->response->numFound == 1){
            echo '<span id="repo-single-result-flag" style="display:none;"></span>';
        }

        // Paging at the bottom
        $rows = @$_GET['rows'];
        if($rows > 0 && $rows < $result->response->numFound){
            
            echo '<div id="repo-pager">';
            
            if($result->response->start > 0){
                $new_start = $result->response->start - $rows;
                if($new_start < 0) $new_start = 0;
                $new_query_string = preg_replace('/start=[0-9]+/', "start=$new_start", $_SERVER['QUERY_STRING']);
                $uri = 'index.php?' . $new_query_string;
                echo '<a href="'.$uri.'">&lt; Previous</a> ';
            }else{
                echo '&lt; Previous ';
            }

            // we might have zillions of pages
            if($result->response->start / $rows > 10){
                $start_page = 10;
            }else{
                $start_page = 0;
            }
            
            $links_rendered = 0;
            for($i = $start_page; $i < ($result->response->numFound/$rows); $i++){
            
                // no more than then next 10
                if($links_rendered > 10){
                    echo "... ";
                    break;
                }
                $links_rendered++;
                
                if($i * $rows == $result->response->start){
                    echo '<span class="repo-current-page" >'. ($i + 1) . "</span> ";
                }else{
                    $new_start = $i * $rows;
                    $new_query_string = preg_replace('/start=[0-9]+/', "start=$new_start", $_SERVER['QUERY_STRING']);
                    $uri = 'index.php?' . $new_query_string;
                    echo '<a href="'.$uri.'">'. ($i + 1) . '</a> ';
                }
            }

            if($result->response->start + count($result->response->docs) >= $result->response->numFound){
                echo 'Next &gt;';
            }else{
                $new_start = $result->response->start + $rows;
                if($new_start > $result->response->numFound) $new_start = $result->response->numFound - $rows;
                $new_query_string = preg_replace('/start=[0-9]+/', "start=$new_start", $_SERVER['QUERY_STRING']);
                $uri = 'index.php?' . $new_query_string;
                echo '<a href="'.$uri.'">Next &gt;</a> ';
            }
            
            echo '</div>';
            
        }
       /*
        echo "<pre>";
        var_dump($result);
        echo "</pre>";
        */
        
?>

    
</div> <!-- content -->
<div id="repo-page-sidebar">
    <h2>Filtering</h2>
    
    <?php
        // we need to find the repeating fq params in the query string
        // that php doesn't do so well
        $facet_queries = array();
        foreach(explode('&', $_SERVER['QUERY_STRING']) as $param){
            list($name, $val) = explode('=', $param);
            if($name == 'fq' ) $facet_queries[] = urldecode($val);
        };
        echo '<hr/>';
        write_facet_select($result, 'item_type', "Item Type", $facet_queries);
        write_facet_select($result, 'country_name', "Country", $facet_queries);
        write_facet_select($result, 'object_created_year', "Year", $facet_queries);
        echo '<hr/>';
        write_facet_select($result, 'family', "Family", $facet_queries);
        write_facet_select($result, 'genus', "Genus", $facet_queries);
        write_facet_select($result, 'epithet', "Epithet", $facet_queries);
        echo '<hr/>';
    
    ?>
    





    <p>&nbsp;</p>
</div><!-- side bar -->
    </form>

<?php include_once('inc/footer.php'); ?>

