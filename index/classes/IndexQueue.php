<?php

class IndexQueue extends SQLite3{
    
    var $db;
  
    public function __construct($queue = 'default'){        
        
        $db_file = "queues/$queue.db";
        
        $this->open($db_file);
        
        // make sure it has a table in it
        $this->exec("CREATE TABLE IF NOT EXISTS index_queue (item_id TEXT PRIMARY KEY, data_file TEXT, priority INTEGER DEFAULT 0, created DATETIME DEFAULT CURRENT_TIMESTAMP );");
        
    }
    
    public function enqueue($item_id, $data_file){
        
        $id = $this->escapeString($item_id);
        $f = $this->escapeString($data_file);
        
        if(!$id){
            echo "No id supplied for enqueued item. Ignoring.\n";
            return;
        }

        if(!$id){
            echo "No data file supplied for enqueued item. Ignoring\n";
            return;
        }

        $result = $this->query("SELECT * FROM index_queue WHERE item_id = '$id'");
        if($result->fetchArray()){
            $this->exec("UPDATE index_queue SET priority = priority + 1 WHERE item_id = '$id'");
        }else{
            $this->exec("INSERT INTO index_queue (item_id, data_file, priority) VALUES ('$id', '$f' , 1)");
        }
        
    }
    
    /**
    *   removes item from queue
    */
    public function dequeue($item_id){
        $id = $this->escapeString($item_id);
        $this->exec("DELETE FROM index_queue WHERE item_id = '$id'");
    }
    
    /**
    *   returns the most important file to index
    */
    public function get_priority_file(){
        $result = $this->query("SELECT data_file FROM index_queue WHERE priority > 0 GROUP BY data_file ORDER BY SUM(priority) DESC, created ASC LIMIT 1");   
        $row = $result->fetchArray(SQLITE3_ASSOC);     
        if($row){       
            return $row['data_file'];
        }else{
            return null;
        }
    }
    
    /**
    * called when a data file is un-available or corrupted
    */
    public function shelve($data_file){
        $this->exec("UPDATE index_queue SET priority = -1 WHERE data_file = '$data_file'");
    }
    
}

?>