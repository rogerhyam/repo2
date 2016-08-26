<?php
     require_once('inc/header.php');
?>
<div class="repo-doc-page">

    <h2>Help</h2>
    
    <p>This is the Royal Botanic Garden Edinburgh digital repository. It aims to be an index to as many of our digital assets as possible and storage location for digital objects that aren't managed by other systems or are shared between systems.</p>
    
    <h3>Search Tips</h3>
    
    <p>Results are returned in relevance order. The more words you search for the greater the number of results will be returned but the more accurate the relevance will be.</p>
    
    <p>Adding a + at the start of a word will require it to be present in search result. e.g. including <code>+Rhododendron</code> forces results that contain the word Rhododendron.</p>
    
    <p>Adding a - at the start of a word will require it NOT to be present in search result. e.g. including <code>-Rhododendron</code> forces results that contain the word Rhododendron to be omitted.</p>
    
    <p>Surrounding several words with double quotes turns them into a phrase that is treated like a single word for relevance matching e.g. <code>"Rhododendron ponticum"</code>.
        You can put a + or - at the start of a phrase to make required or not e.g. <code>+"Rhododendron ponticum"</code></p>
    
    <h4>Filtering</h4>
    
    <p>
        Filters are displayed on the right of any search results. They allow you to further restrict results to items that have been tagged in certain ways.
        Be aware that not all items are fully tagged to the different categories. e.g. A document may mention "China" but not be tagged as "China" - perhaps because that isn't its main subject or a human
        hasn't had a chance to categorise it. It will be found in a search for <code>+China</code> but then may be eliminated if results are filtered to just China.
    </p>
    
    <h3>Support</h3>
    <p>If you have any questions or suggestions please contact Roger Hyam &lt;<a href="mailto:r.hyam@rbge.org.uk?subject=Digital Repository: ">r.hyam@rbge.org.uk</a>&gt;</p>.

</div>
<?php
    include_once('inc/footer.php');
?>
