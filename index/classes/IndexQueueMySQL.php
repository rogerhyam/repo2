<?php

class IndexQueueMySQL{
    
    var $mysqli;
    var $table;
    var $enqueue_stmt;
    var $dequeue_stmt;
    var $get_file_stmt;
    var $shelve_stmt;
  
    public function __construct($queue = 'default'){        
        
        // get a connection to mysql for use
        $this->mysqli = new mysqli(REPO_DB_HOST, REPO_DB_USER, REPO_DB_PASSWORD, REPO_DB_DATABASE);    

        // connect to the database
        if ($this->mysqli->connect_error) {
          error_log("IndexQueueMySQL: " + $mysqli->connect_error);
        }
        
        // wobble if we can't set the character set
        if (!$this->mysqli->set_charset("utf8")) {
          error_log("Error loading character set utf8: %s\n", $mysqli->error);
        }
        
        // set the table we are using for this queue
        $this->table = 'index_queue_' . $queue;
        
        // if the queue table doesn't exist create it.
        $this->mysqli->query("CREATE TABLE IF NOT EXISTS $this->table (item_id VARCHAR(500) PRIMARY KEY, data_file VARCHAR(500), priority INT DEFAULT 0, created DATETIME DEFAULT CURRENT_TIMESTAMP );");
        
        // set up the statements we will be using
        $this->enqueue_stmt = $this->mysqli->prepare("INSERT INTO $this->table (item_id, data_file, priority) VALUES (?, ? , 1) ON DUPLICATE KEY UPDATE priority = priority + 1");
        $this->dequeue_stmt = $this->mysqli->prepare("DELETE FROM $this->table WHERE item_id = ?");
        $this->get_file_stmt = $this->mysqli->prepare("SELECT data_file FROM $this->table WHERE priority > 0 GROUP BY data_file ORDER BY SUM(priority) DESC LIMIT 1;");
        $this->shelve_stmt = $this->mysqli->prepare("DELETE FROM $this->table WHERE data_file = ?");
        
    }
    
    public function enqueue($item_id, $data_file){
        
        if(!$item_id){
            error_log("IndexQueueMySQL: No id supplied for enqueued item. Ignoring.");
            return;
        }

        if(!$data_file){
            error_log("IndexQueueMySQL: No data file supplied for enqueued item. Ignoring.");
            return;
        }
        
        $this->enqueue_stmt->bind_param("ss", $item_id, $data_file);
        if(!$this->enqueue_stmt->execute()){
            error_log("IndexQueueMySQL: Failed to enqueue item. $item_id " . $this->enqueue_stmt->error);
        }

        $this->enqueue_stmt->reset();

    }
    
    /**
    *   removes item from queue
    */
    public function dequeue($item_id){
        
        $this->dequeue_stmt->bind_param("s", $item_id);
        if(!$this->dequeue_stmt->execute()){
            error_log("IndexQueueMySQL: Failed to dequeue item. $item_id " . $this->dequeue_stmt->error);
        }
        $this->dequeue_stmt->reset();
    }
    
    /**
    *   returns the most important file to index
    */
    public function get_priority_file(){      
        
        if(!$this->get_file_stmt->execute()){
            error_log("IndexQueueMySQL: Failed to find priority item. " . $this->get_file_stmt->error);
        }
        $this->get_file_stmt->bind_result($data_file);
        $this->get_file_stmt->fetch();
        $this->get_file_stmt->reset();
        
        return $data_file;
        
    }
    
    /**
    * called when a data file is un-available or corrupted
    */
    public function shelve($data_file){
        
        $this->shelve_stmt->bind_param("s", $data_file);
        if(!$this->shelve_stmt->execute()){
             error_log("IndexQueueMySQL: Failed to shelf item. $data_file" . $this->shelve_stmt->error);
        }

    }
    
}

?>