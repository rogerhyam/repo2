<?php

    // returns results of a search for a single item with its derivatives by a field value in the appropriate format
    
    
    // e.g. http://repo.rbge.org.uk/service/item/xml/guid?id=http://data.rbge.org.uk/herb/E00137838
    // RewriteRule ^service/item/([^/]*)/guid srv.php?srv_name=item&response_format=$1&id_kind=guid [QSA,NC]
    
    print_r($_GET);




?>