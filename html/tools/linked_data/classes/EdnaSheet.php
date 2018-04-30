<?php

require_once('tools/linked_data/classes/GenericSheet.php');

class EdnaSheet extends GenericSheet{
    
    var $item_type = 'DNA Sample'; // the item_type of this thing in the index - will be overriden in implementing classes
    var $mysql_table = 'linked_edna'; // the mysql table storing the rows for the items


    // see doc in GenericSheet
    var $field_map = array(

       array(
           'name' => 'first_names',
           'mysql_type' => 'varchar(100)',
           'regex' => '/.*/',
           'lookup_required' => true,
           'help'  => 'this is some help on the field'
       ),
       array(
           'name' => 'surname',
           'mysql_type' => 's'
       ),
       array(
           'name' => 'age',
           'mysql_type' => 'i'
       ),
       array(
           'name' => 'date',
           'mysql_type' => 's'
       ),
       array(
           'name' => 'title',
           'mysql_type' => 's'
       )
    );
    
}

?>