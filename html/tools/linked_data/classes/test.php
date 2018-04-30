<?php

require_once('tools/linked_data/classes/EdnaSheet.php');

$test_data = file('tools/linked_data/classes/test_data.csv');

$processor = new EdnaSheet($test_data);

while($processor->next());

print_r($processor->errors);

?>