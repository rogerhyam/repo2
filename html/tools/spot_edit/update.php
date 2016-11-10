<?php
require_once( '../../../config.php' );

// check authentication & authorisation
require_once( '../tools_config.php' );

// load data file
$data_path = $_POST['data_path'];
$all_docs = json_decode(file_get_contents($data_path));
foreach($all_docs as $doc){
    if($doc->id == $_POST['id']) break;
    else $doc = false;
}
if(!$doc){
    echo "Couldn't find metadata document to edit with id " . $_POST['id'];
    exit;
}

// work through the $_POST variables and update the data

foreach($_POST as $key => $value){

    // values we don't touch
    if ($key == 'id') continue;
    if ($key == 'data_path') continue;
    if ($key == 'data_location') continue;
    
    // behave differently for multivalue fields
    if(is_array($value)){
        $doc->$key = array_filter($value);
    }else{
        $doc->$key = $value;
    }
}

// save the json back to the file
$json_out = json_encode($all_docs);
file_put_contents($data_path, $json_out);

// queue it for re-indexing
require_once('../../../index/classes/IndexQueue.php');
$queue = new IndexQueue('edited_items');
$queue->enqueue($_POST['id'], base64_decode($_POST['data_location']));

// because we may be creating this file as www-data which would prevent the indexer accessing it
// we make it very permissive - this is a bit hacky as we shouldn't know about the location of the file
chmod(INDEX_QUEUE_PATH . "/edited_items.db", 0777);

// return to the form
$form_uri = '/tools/spot_edit/index.php?data_location=' . $_POST['data_location'];
header("Location: $form_uri");

?>
<pre>
<?php print_r($_POST); ?>
<?php print_r($doc); ?>
</pre>