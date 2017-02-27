# repo2
The second attempt at a simple Solr based repository for RBGE

# Is this for you?

If you are looking for a working system this probably isn't for you as it is very much a custom system for a custom job but you are welcome to use the code examples here.

# Keep relationship concepts

## Derivation 

Documents have derived_from relationships. A set of fields in copied from source to the derived target at index time. This means that, for example, 'plants' can inherit Genus and Family names from 'accessions' and item_images can do likewise. 

Documents have a rank and derivation goes down the ranks only - to prevent circularity and race conditions with reindexing.

Derivations are used in limited and controlled ways. Accession -> Plants -> Photos or Directory -> Directory -> PDF.

## Annotation

Documents can have annotation_of relationships. This enables data to flow "up the way". For example a plant can be annotated with a geospatial point and the value of the geolocation field will be copied from the annotation up to the plant at index time.

Annotations can't be chained or have derivation relationships - lets keep this simple.

## See Also

Documents can have a list of see_also links to other documents. Data doesn't flow along these links they are for linking to data sources that may have been used in augmenting the item during indexing. A plant may have a value added to vernacular_ss  but this would not contain info about where it came from so a see_also is added pointing to the vernacular name document.  Likewise with CITES codes or red list status.

## Annotation vs See Also

Annotation are peculiar to the thing being annotated (e.g. and OCR of the text) whilst See Also's are common to multiple documents (e.g. the same vernacular name will be referenced by multiple documents).



# Installing SOLR

Following instructions here https://www.digitalocean.com/community/tutorials/how-to-install-solr-5-2-1-on-ubuntu-14-04

sudo apt-get install python-software-properties

sudo apt-get update

sudo apt-get install oracle-java8-installer

wget http://mirrors.muzzy.org.uk/apache/lucene/solr/6.1.0/solr-6.1.0.tgz

tar xzf solr-6.1.0.tgz solr-6.1.0/bin/install_solr_service.sh --strip-components=2

sudo bash ./install_solr_service.sh solr-6.1.0.tgz

- that gets it going with no collection

Then create a collection based on data driven schema

sudo su - solr -c "/opt/solr/bin/solr create -c rbge01 -n data_driven_schema_configs"

Then copy over the rbge schema ...

# Adding components to Apache

sudo apt-get install php-curl
sudo apt-get install php-gd
sudo apt-get install php-sqlite3
sudo apt-get install php-zip
sudo apt-get install php-dom
sudo apt-get install php-imagick
sudo a2enmod rewrite
sudo a2enmod headers

and allow rewrite in .htaccess 


mkdir /var/www/index/queues
sudo chown -R roger:www-data /var/www/index/queues

# Useful commands
http://repo.rbge.org.uk/id/reference_doc
curl http://localhost:8983/solr/gettingstarted/update --data '<delete><query>*:*</query></delete>' -H 'Content-type:text/xml; charset=utf-8'
curl http://localhost:8983/solr/rbge01/update --data '<delete><query>*:*</query></delete>' -H 'Content-type:text/xml; charset=utf-8'

## delete from a certain date
curl http://localhost:8983/solr/rbge01/update --data '<delete><query>indexed_at: [2016-08-14T00:00:00Z TO NOW]</query></delete>' -H 'Content-type:text/xml; charset=utf-8'

curl http://localhost:8983/solr/gettingstarted/update --data '<commit/>' -H 'Content-type:text/xml; charset=utf-8'
curl http://localhost:8983/solr/rbge01/update --data '<commit/>' -H 'Content-type:text/xml; charset=utf-8'

sudo cp /var/solr/data/gettingstarted/conf/managed-schema /var/www/

sudo cp  /var/www/managed-schema /var/solr/data/rbge01/conf/managed-schema

# Indexing Stories.
http://stories.rbge.org.uk/feed?modified=true&paged=0&orderby=modified&order=ASC

# Getting WKHTMLtoPDF working

https://coderwall.com/p/tog9eq/using-wkhtmltopdf-and-an-xvfb-daemon-to-render-html-to-pdf

# Getting Tika extract working

make the upload size bigenough
solrconfig.xml file: <requestDispatcher handleSelect=”true”> <requestParsers enableRemoteStreaming=”false” multipartUploadLimitInKB=”10240″ />

curl "http://localhost:8983/solr/gettingstarted/update/extract?&extractOnly=true&wt=json&indent=true" -F "myfile=@stories.pdf"

-F "myfile=@example/exampledocs/solr-word.pdf"

# icons

 http://bbc.github.io/gel-iconography/
 
 # fix a sqlite3 db that is stuck after crash
 
 echo ".dump" | sqlite old.db | sqlite new.db
 
# check out the validity of jpegs
 nohup find . -name "*.jpg" | jpeginfo -c -f - > ~/jpeg_check.jpg &
 
 