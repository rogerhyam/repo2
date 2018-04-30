<?php

/*
    Base class for spread sheet ingesters.
     - A child class is created with an array of lines from a CSV input
     - each field in each row is validated 
     - if validation errors occur they are stored
     - if no validation errors then will populate prepared statement and run them
    
*/

class GenericSheet{
    
    var $lines; // the lines from the csv input
    var $rows; // lines parsed into arrays of fields
    var $errors; // an array of error messages
    var $current_row; // index of row we are on
    var $current_col; // index of col we are on
    
    var $item_type = 'default'; // the item_type of this thing in the index - will be overriden in implementing classes
    var $mysql_table = 'linked_default'; // the mysql table storing the rows for the items
    
    
    /*
     the field map is overridden by subclasses to specify
     how they handle the different columns.
    
     Each field has these properties. * are required
     
     name* - this is the name in the db and in the solr index
     mysql_type* - the column type in the mysql db
     regex - if present then value must match this regex before other tests
     lookup_required - if true then this can't be a new value it has to already exist in the target field in the index
     help - text to show the user on failure
    
    */
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
    
    
    public function __construct($lines_data){
        $this->lines = $lines_data;
        $this->rows = array_map('str_getcsv', $lines_data);
        $this->errors = array();
        $this->current_row = 0;
        $this->current_col = -1;
    }
    
    /*
     * process the next field value
     * return true unless there are no more fields
     * to be processed or max_errors is reached
     */
    public function next($max_errors = -1){
        
        $this->current_col++;
        
        // do we need to move to the next row
        if( $this->current_col > count($this->rows[0]) -1 ){
            $this->current_row++;
            $this->current_col = 0;
        
            // run out of rows
            if($this->current_row >= count($this->rows)) return false;
        }
        
        if($this->validate_current_field()){
            // things are good so return OK
            return true;
        }else{
            // validation failed - have we run out of errors
            if($max_errors > 0 && count($this->errors) >= $max_errors){
                return false;
            }
        }
        
    }
    

    protected function validate_current_field(){
        
        // if we are beyond the end of the number of fields in the field map we just ignore the extras
        if($this->current_col >= count($this->field_map)) return true;
        
        // get the field definition out of the map
        $field = $this->field_map[$this->current_col];
        
        // the value we are testing
        $value = trim($this->rows[$this->current_row][$this->current_col]);
        
        // firstly check the regex matches
        if(isset($field['regex'])){
            if(!preg_match($field['regex'], $value)){
                $this->record_error('The value does not match the specified format.');
                return false;
            }
        }
        
        // if the value has to exist in the index - check that
        if(isset($field['lookup_required']) && $field['lookup_required']){
            
            if(!$this->value_exists($field['name'], $value)){
                $this->record_error('The value does must already exist in the index.');
                return false;
            }
            
        }
        
        // derived_from is a special field that contains a link to an existing 
        // index item but the value may need expanding into a full URI from a basic barcode or accession
        if($field['name'] == 'derived_from'){
            $parent_id = $this->get_parent_id($value);
            if(!$parent_id || !value_exists('id', $parent_id)){
                $this->record_error('The value must map to an existing repository item id.');
                return false;
            }
        }
        
        
        return true;
        
        
    }
    
    /*
        By default this assumes value is a barcode or accession number 
        but could be overriden in other classes
    */
    protected function get_parent_id($value){
        
        // case it is a barcode
        if(preg_match('/^E[0-9]{8}$/', $value)){
            return 'http://data.rbge.org.uk/herb/' . $value;
        }
        
        // case it is an accession
        if(preg_match('/^[0-9]{8}[A-Za-z]*/')){
            return 'http://data.rbge.org.uk/living/' . $value;
        }
        
    }

    /*
     * Check to see if this value exists in this field in the index already
     */
    protected function value_exists($field_name, $value){
        // query solr
        return true;
    }
    

    protected function record_error($message = ''){
        
        $field = $this->field_map[$this->current_col];
        
        if(isset($field['help'])){
            $message .= "\n" . $field['help'];
        }
        
        $this->errors[] = "Row: " . ($this->current_row +1) . " Col: " . ($this->current_col +1) . "\n" . $message;
        
    }
    

}




?>