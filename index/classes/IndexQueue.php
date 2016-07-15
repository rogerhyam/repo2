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
        $this->exec("INSERT INTO index_queue (item_id, data_file, priority) VALUES ('second', 'third', 8)");
        //$result = $this->query('select count(*) as n from index_queue');
        $result = $this->query('select * from index_queue');
        print_r($result->fetchArray(SQLITE3_ASSOC));
    }
    
}

?>