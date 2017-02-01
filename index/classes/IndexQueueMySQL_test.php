<?php
    require_once('../config.php');
    require_once('classes/IndexQueueMySQL.php');
    
    echo "Creating class\n";
    $queue = new IndexQueueMySQL('test');
    
    echo "Queuing items\n";
    for ($i=0; $i < 100; $i++) { 
        $queue->enqueue(
            "http://repo.rbge.org.uk/id/file_drop/2017/rhyam/02/01/09-52-30/DSC_0247.JPG_" . $i,
            "/file_drop/2017/rhyam/02/01/09-52-30/DSC_0247.json_" . $i
        );
    }
    // some of them twice
    for ($i=0; $i < 10; $i++) { 
        $queue->enqueue(
            "http://repo.rbge.org.uk/id/file_drop/2017/rhyam/02/01/09-52-30/DSC_0247.JPG_" . $i,
            "/file_drop/2017/rhyam/02/01/09-52-30/DSC_0247.json_" . $i
        );
    }
    
    echo "Get some files to index\n";
    for ($i=0; $i < 10; $i++) { 
        echo $queue->get_priority_file();
        echo "\n";
        $queue->dequeue("http://repo.rbge.org.uk/id/file_drop/2017/rhyam/02/01/09-52-30/DSC_0247.JPG_" . $i);
    }
    
    echo "Dequeuing items\n";
    for ($i=0; $i < 122; $i++) { 
        $file = $queue->dequeue(
            "http://repo.rbge.org.uk/id/file_drop/2017/rhyam/02/01/09-52-30/DSC_0247.JPG_" . $i
        );
    }
    
    
    echo "Should be none remaining\n";
    echo $queue->get_priority_file();
    echo "\n";
    echo "-- finished --\n";
    

?>
