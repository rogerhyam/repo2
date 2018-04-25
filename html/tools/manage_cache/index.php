<?php
	require_once( '../../../config.php' );
    require_once( '../tools_config.php' );
    
	$include_css[] = "/tools/manage_cache/manage_cache.css";
    require_once( '../../inc/header.php' );
	
?>  

<div class="repo-doc-page" id="repo-tools-manage-cache">
    <h2>Manage Cache</h2>
    <p>The cache is used to increase the efficiency of displaying or distributing images of a particular size.
		Rather than downsize an image from the original everytime it is requested a copy is kept on the first request and used again if that size of that images is requested a second time.</p>
	<p>Unfortunately this means the cache can build to the point that it has a copy of every image at every size that has ever been requested. To prevent this happening older images 
		are automatically deleted from the cache so as to keep it a particular size that improves performance without filling the disk completely.</p>
	<p>This tool is to check that the automated process is working and to trigger manual trimming or total refreshing of the cache.</p>
	
	<table>
		<tr>
			<th>Cache Directory</th>
			<td> 
		<?php
			$cache_dir_path = realpath ( '../../cache' );
			echo $cache_dir_path;
		?>
			</td>
		</tr>
		<tr>
			<th>Cache Size</th>
			<td>
		<?php
			$io = popen ( '/usr/bin/du -skh ' . $cache_dir_path, 'r' );
			$size = fgets ( $io, 4096);
			$size = substr ( $size, 0, strpos ( $size, "\t" ) );
			pclose ( $io );
			echo $size;
		?>
			</td>
		</tr>
		<tr>
			<th>Remove File Older Than</th>
			<td>
				<form action="trim_cache.php" method="GET">
					<select name="days">
						<option value="0">0 days</option>
						<option value="1">1 day</option>
						<option value="2">2 days</option>
						<option value="3">3 days</option>
						<option value="4">4 days</option>
						<option value="5">5 days</option>
						<option value="6">6 days</option>
						<option value="7" selected>1 week</option>
						<option value="14">2 weeks</option>
						<option value="21">3 weeks</option>
						<option value="28">4 weeks</option>
					</select>
					<input type="submit" value="Do it!" />
				</form>
			</td>
		</tr>
	</table>
	

	
	
</div>	
<?php
    require_once( '../../inc/footer.php' );
?>
	