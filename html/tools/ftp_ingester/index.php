<?php
	require_once( '../../../config.php' );
    require_once( '../tools_config.php' );
    
    require_once( '../../inc/header.php' );
	
?>  

<div class="repo-doc-page" id="repo-tools-ftp-ingester">
    <h2>FTP Ingester</h2>
    <p>Use this tool to bulk import sets of files. There is FTP access to directories on the server. Each directory is for a different class of data (e.g. accession linked photos). Files are uploaded to the directory along with an index file that contains the metadata for the files in the directory.</p>
	
	<p>This tool will validate that the files and the metadata in the directory are valid and, if they are, allow an ingest process to run.</p>
	
	<p>Clicking the links below to validate the contents of the corresponding FTP directories.</p>
	
	<ul>
		<li>
			<p><a href="item_images.php">Item Images</a> - Images of existing repository item such as accessions, plants, herbarium specimens that can be linked to a specific accession number or barcode.
			<br/>Directory name: item_images.
			<br/>meta.csv should contain columns: filename; accession/barcode; photographer. The first row is assumed to be headings and ignored. Any columns beyond the third are ignored.</p>
		</li>
	</ul>
	
	<h3>FTP Credentials</h3>
	
	<p>This is only accessible from within the RBGE network and these credentials can only be used for FTP access.</p>
	<p><strong>Server: </strong>repo.rbge.org.uk</p>
	<p><strong>Username: </strong>ftpingest</p>
	<p><strong>Password: </strong>LookBusy</p>
	
	
</div>	
<?php
    require_once( '../../inc/footer.php' );
?>
	