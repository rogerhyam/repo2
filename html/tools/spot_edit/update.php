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
require_once('../../../index/classes/IndexQueueMySQL.php');
$queue = new IndexQueueMySQL('edited_items');
$queue->enqueue($_POST['id'], base64_decode($_POST['data_location']));

// return to the form
$form_uri = '/tools/spot_edit/index.php?data_location=' . $_POST['data_location'];
header("Location: $form_uri");

?>
<pre>
<?php print_r($_POST); ?>
<?php print_r($doc); ?>
</pre>