# repo2
The second attempt at a simple Solr based repository for RBGE

# Is this for you?

If you are looking for a working system this probably isn't for you as it is very much a custom system for a custom job but you are welcome to use the code examples here.


# Useful commands

curl http://localhost:8983/solr/gettingstarted/update --data '<delete><query>*:*</query></delete>' -H 'Content-type:text/xml; charset=utf-8'

curl http://localhost:8983/solr/gettingstarted/update --data '<commit/>' -H 'Content-type:text/xml; charset=utf-8'

sudo cp /var/solr/data/gettingstarted/conf/managed-schema /var/www/


# Getting Tika extract working

make the upload size bigenough
solrconfig.xml file: <requestDispatcher handleSelect=”true”> <requestParsers enableRemoteStreaming=”false” multipartUploadLimitInKB=”10240″ />

curl "http://localhost:8983/solr/gettingstarted/update/extract?&extractOnly=true&wt=json&indent=true" -F "myfile=@stories.pdf"

-F "myfile=@example/exampledocs/solr-word.pdf"