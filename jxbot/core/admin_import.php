<?php
/********************************************************************************
 *  JxBot - conversational agent for the web
 *  Copyright (c) 2015 Joshua Hawcroft
 *
 *      May all beings have happiness and the cause of happiness.
 *      May all beings be free of suffering and the cause of suffering.
 *      May all beings rejoice in the happiness of others.
 *      May all beings abide in equanimity; free of attachment and delusion.
 * 
 *  Redistribution and use in source and binary forms, with or without
 *  modification, are permitted provided that the following conditions are met:
 *
 *  1) Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *  2) Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in the
 *     documentation and/or other materials provided with the distribution.
 *
 *  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 *  ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 *  WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 *  DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS BE LIABLE FOR ANY
 *  DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 *  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 *  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 *  ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 *  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 *  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *******************************************************************************/

if (!defined('JXBOT_ADMIN')) die('Direct script access not permitted.');





if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'purge-do'))
	JxBotNLData::purge_categories();

if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'purge'))
	page_warn_purge();
else
	page_import_form();


function page_warn_purge()
{
?>
<h2>Purge</h2>

<p><strong>Warning: You are about to purge all categories from the database. Are you sure?</strong></p>

<p><button name="action" value="cancel">Cancel</button> <button name="action" value="purge-do">Purge</button></p>

<?php
}



function do_delete_file()
{
	$inputs = JxBotUtil::inputs('file');
	$file = JxBotConfig::aiml_dir() . str_replace(array('/', './', '../'), '', $inputs['file']);
	unlink($file);
}


	
function do_handle_upload()
{
	if ($_FILES['data_file']['error'] === UPLOAD_ERR_OK)
	{
		$file_name = $_FILES['data_file']['name'];
		$extension = pathinfo($file_name)['extension'];
		if (strtolower($extension) != 'aiml')
			print '<p>Invalid file format.</p>';
		else
		{
			$in_dest = JxBotConfig::aiml_dir() . $file_name;
			if (!@move_uploaded_file($_FILES['data_file']['tmp_name'], $in_dest))
				print '<p>Couldn\'t save file. Check the permissions on the aiml directory.</p>';
			else
				print '<p>File uploaded successfully.</p>';
		}
	}
	else
		print '<p>Error uploading file.</p>';
}


function page_import_form()
{
?>

<?php 
if (isset($_FILES['data_file']) && ($_POST['action'] == 'upload')) do_handle_upload(); 
if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'delete-file')) do_delete_file();
?>

<h2>Bulk Reload</h2>

<?php
show_server_files();
?>

<?php

if (isset($_POST['action']) && ($_POST['action'] == 'bulk-reload'))
	bulk_reload();

?>

<p><button type="submit" name="action" value="bulk-auto">Bulk Load</button> <button type="submit" name="action" value="purge">Purge All</button></p>


<h2>Upload File</h2>

<p><label for="data_file">File:</label>
<input type="file" name="data_file" id="data_file" size="50"></p>

<p><button type="submit" name="action" value="upload">Upload File</button></p>





<?php
}


function server_file_list()
{
	$dir = JxBotConfig::aiml_dir();
	
	$list = array();
	$index = array();
	
	$stmt = JxBotDB::$db->prepare('SELECT id,name,status,last_update FROM file ORDER BY name');
	$stmt->execute();
	$rows = $stmt->fetchAll(PDO::FETCH_NUM);
	foreach ($rows as $row)
	{
		$index[$row[1]] = count($list);
		$list[] = array($row[0], $row[1], $row[2]);
	}

	$dh = opendir($dir);
	while (($file = readdir($dh)) !== false)
	{
		if (substr($file, 0, 1) == '.') continue;
		if (strtolower(pathinfo($file)['extension']) != 'aiml') continue;
		
		if (!isset($index[$file]))
			$list[] = array(null, $file, 'Not Loaded');
	}
	closedir($dh);
	
	return $list;
}



function show_server_files()
{
	$list = server_file_list();
	$next_file = null;
?><table style="width: auto; min-width: 30em;">
<tr>
	<th>File</th>
	<th style="width: 7em;">Status</th>
	<th style="width: 1.5em;"></th>
</tr>
<?php
	foreach ($list as $file)
	{
		print '<tr>';
		print '<td>'.$file[1].'</td>';
		print '<td>'.$file[2].'</td>';
		print '<td><a href="?page=import&action=delete-file&file='.$file[1].'">';
		JxWidget::small_delete_icon();
		print '</a></td>';
		print '</tr>';
		
		if (($file[2] == 'Not Loaded') ||
			($file[2] == 'Has Update'))
		{
			if ($next_file === null) $next_file = $file[1];
		}
	}
?></table><?php

	/* if the user has requested automatic load,
	include a javascript which will use AJAX to request the next import */
	if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'bulk-auto') &&
		$next_file !== null)
	{
?><script type="text/javascript">

var req = new XMLHttpRequest();
req.onreadystatechange = function() 
{
	if (req.readyState == 4)
		window.location = '?page=import&action=bulk-auto';
};
req.open('GET', '?ajax=load&file=<?php print $next_file; ?>', true);
req.send();

</script><?php
	}
	
}


function bulk_reload()
{

// regarding sending updates to the browser:
// best bet is to update the file listing in a table,
// put status: loaded, errors, loading into the table for each file
// have a periodic refresh that enquires as to the upload progress

// another option,
// provide the list, and let javascript run through the list
// and send an ajax request/normal page request for each

	print '<p>';
	
	print 'Purging existing database...';
	JxBotNLData::purge_categories();
	
	print '<span class="green">DONE</span>. <br>';

	$aiml_directory = dirname(dirname(__FILE__)).'/aiml/';
	
	$dh = opendir($aiml_directory);
	while (($file = readdir($dh)) !== false)
	{
		if (substr($file, 0, 1) == '.') continue;
		if (pathinfo($file)['extension'] != 'aiml') continue;
		
		print 'Importing '.$file.'...';
		
		$filename = $aiml_directory . $file;
		$importer = new JxBotAimlImport();
		$result = $importer->import($filename);
		
		if (is_array($result)) 
		{
			print '<span class="green">DONE</span><br>';
			print implode('<br>', $result);
		}
		else
			print '<span class="red">'.$result.'</span>';
		
		print '<br>';
	}
	closedir($dh);
	
	print '</p>';
}


