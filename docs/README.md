# repo2
The second attempt at a simple Solr based repository for RBGE

# Is this for you?

If you are looking for a working system this probably isn't for you as it is very much a custom system for a custom job but you are welcome to use the code examples here.

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




# Useful commands

curl http://localhost:8983/solr/gettingstarted/update --data '<delete><query>*:*</query></delete>' -H 'Content-type:text/xml; charset=utf-8'

curl http://localhost:8983/solr/gettingstarted/update --data '<commit/>' -H 'Content-type:text/xml; charset=utf-8'

sudo cp /var/solr/data/gettingstarted/conf/managed-schema /var/www/

sudo cp  /var/www/managed-schema /var/solr/data/gettingstarted/conf/managed-schema




# Getting Tika extract working

make the upload size bigenough
solrconfig.xml file: <requestDispatcher handleSelect=”true”> <requestParsers enableRemoteStreaming=”false” multipartUploadLimitInKB=”10240″ />

curl "http://localhost:8983/solr/gettingstarted/update/extract?&extractOnly=true&wt=json&indent=true" -F "myfile=@stories.pdf"

-F "myfile=@example/exampledocs/solr-word.pdf"