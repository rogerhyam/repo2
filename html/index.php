<?php 

    // before we do anything we redirect if we haven't got a q
    // we always need to run a query so we have the facets available.
    if (!@$_GET['q']) {
        header('Location: index.php?rows=0&q=*&facet=true&facet.mincount=1&facet.limit=100&facet.field=genus&facet.field=family&facet.field=epithet');
    }
    
    require_once('inc/header.php');
?>
<div id="repo-page-wrap">
     <form class="repo-search-form" method="GET" action="index.php">
<div id="repo-page-content">
   
        <input type="text" name="q" id="repo-input-q" value="<?php echo @$_GET['q'] ?>"/>
        <input type="hidden" name="start" id="repo-input-start" value="<?php echo @$_GET['start'] ? $_GET['start'] : 0;  ?>" />
        <input type="hidden" name="rows" value="<?php echo REPO_SOLR_PAGE_SIZE ?>" />
        <input type="hidden" name="facet" value="true" />
        <input type="hidden" name="facet.sort" value="count" />
        <input type="hidden" name="facet.mincount" value="1" />
        <input type="hidden" name="facet.limit" value="100" />
        <input type="hidden" name="facet.field" value="genus" />
        <input type="hidden" name="facet.field" value="family" />
        <input type="hidden" name="facet.field" value="epithet" />
        
        <input type="submit" value="Search"/>
    
    
<?php 
        $uri = REPO_SOLR_URI . '/query?'. $_SERVER['QUERY_STRING'];
        $ch = curl_init($uri);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );        
        $result = json_decode(curl_exec($ch));
        curl_close($ch);
        
        echo "<p>Showing {$result->response->start} to ". count($result->response->docs) ." of {$result->response->numFound} results found.</p>";
        
        foreach($result->response->docs as $doc){
            echo '<div class="repo-search-result">';
            echo "<h3>";
            echo $doc->title[0];
            echo "</h3>";
            echo "</div>";
        }


        // Paging at the bottom
        $rows = $_GET['rows'];
        if($rows > 0){
            
            echo '<div class="repo-pager">';
            
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
                    echo "...";
                    break;
                }
                $links_rendered++;
                
                if($i * $rows == $result->response->start){
                    echo ($i + 1) . " ";
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
       
        echo "<pre>";
        var_dump($result);
        echo "</pre>";

        
?>

    
</div> <!-- content -->
<div id="repo-page-sidebar">
    <h2>Filtering</h2>
    
    <?php
        // we need to find the repeating fl params in the query string
        // that php doesn't do so well
        $fqs = array();
        foreach(explode('&', $_SERVER['QUERY_STRING']) as $param){
            list($name, $val) = explode('=', $param);
            if($name == 'fq' ) $fqs[] = urldecode($val);
        };
    
    ?>
    
<?php if(isset($result->facet_counts->facet_fields->family)){ ?>
    <p>
        <strong>Family:</strong>        
        <select name="fq" onchange="this.form.submit();">
            <option value="family:*">~ All ~</option>
<?php
    for($i = 0; $i < count($result->facet_counts->facet_fields->family); $i = $i + 2){
        $name = $result->facet_counts->facet_fields->family[$i];
        $count = $result->facet_counts->facet_fields->family[$i +1];
        $selected = in_array("family:" . $name, $fqs) ? 'selected': '';
        echo "<option value=\"family:$name\" $selected >$name ($count)</option>";
    }
?>
        </select>
    </p>
<?php } //end check for family ?>



<?php if(isset($result->facet_counts->facet_fields->genus)){ ?>
    <p>
        <strong>Genus:</strong>        
        <select name="fq" onchange="this.form.submit();">
            <option value="genus:*">~ All ~</option>
<?php
    for($i = 0; $i < count($result->facet_counts->facet_fields->genus); $i = $i + 2){
        $name = $result->facet_counts->facet_fields->genus[$i];
        $count = $result->facet_counts->facet_fields->genus[$i +1];
        $selected = in_array("genus:" . $name, $fqs) ? 'selected': '';
        echo "<option value=\"genus:$name\" $selected >$name ($count)</option>";
    }
?>
        </select>
    </p>
<?php } //end check for genus ?>

<?php if(isset($result->facet_counts->facet_fields->epithet)){ ?>
    <p>
        <strong>Epithet:</strong>        
        <select name="fq" onchange="this.form.submit();">
            <option value="epithet:*">~ All ~</option>
<?php
    for($i = 0; $i < count($result->facet_counts->facet_fields->epithet); $i = $i + 2){
        $name = $result->facet_counts->facet_fields->epithet[$i];
        $count = $result->facet_counts->facet_fields->epithet[$i +1];
        $selected = in_array("epithet:" . $name, $fqs) ? 'selected': '';
        echo "<option value=\"epithet:$name\" $selected >$name ($count)</option>";
    }
?>
        </select>
    </p>
<?php } //end check for genus ?>




    <p>&nbsp;</p>
</div><!-- side bar -->
    </form>
</div> <!-- page wrap -->

<?php include_once('inc/footer.php'); ?>

