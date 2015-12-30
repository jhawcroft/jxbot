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



JxWidget::tabs(array(
	array('Import AIML', '?page=import', 'subpage', ''),
	array('Log', '?page=import&subpage=logs', 'subpage', 'logs'),
));


$subpage = JxBotUtil::inputs('subpage');
if ($subpage['subpage'] == 'logs')
	require_once('admin_file_log.php');
else
{

	if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'purge-do'))
		JxBotNLData::purge_categories();

	if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'purge'))
		page_warn_purge();
	else
		page_import_form();
}


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
	
	$stmt = JxBotDB::$db->prepare('DELETE FROM file WHERE name=?');
	$stmt->execute(array( $inputs['file'] ));
				
	@unlink($file);
}


	
function do_handle_upload()
{
	if ($_FILES['data_file']['error'] === UPLOAD_ERR_OK)
	{
		$file_name = $_FILES['data_file']['name'];
		
		$extension = pathinfo($file_name);
		if (!isset($extension['extension'])) $extension = '';
		else $extension = strtolower($extension['extension']);
		
		if ($extension != 'aiml')
			print '<p>Invalid file format.</p>';
		else
		{
			$in_dest = JxBotConfig::aiml_dir() . $file_name;
			if (!@move_uploaded_file($_FILES['data_file']['tmp_name'], $in_dest))
				print '<p>Couldn\'t save file. Check the permissions on the aiml directory.</p>';
			else
			{
				//print '<p>File uploaded successfully.</p>';
				JxBotNLData::set_file_status( $file_name, 'Not Loaded' );
			}
		}
	}
	else
		print '<p>Error uploading file.</p>';
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
		$info = pathinfo($file);
		if (!isset($info['extension'])) continue;
		$ext = strtolower($info['extension']);
		if ($ext != 'aiml') continue;
		
		if (!isset($index[$file]))
		{
			$list[] = array(null, $file, 'Not Loaded');
			
			$stmt = JxBotDB::$db->prepare('INSERT INTO file (name, status) VALUES (?, ?)');
			$stmt->execute(array( $file, 'Not Loaded' ));
		}
	}
	closedir($dh);
	
	return $list;
}




function page_import_form()
{

	if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'bulk-load'))
		JxBotAsyncLoader::schedule_all();
		
	if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'load-abort'))
		JxBotAsyncLoader::stop_loading();
		
	if (isset($_REQUEST['action']) && (substr($_REQUEST['action'], 0, 12) == 'file-toggle-'))
	{
		//print 'Toggle '.substr($_REQUEST['action'], 12);
		JxBotAsyncLoader::toggle_file(substr($_REQUEST['action'], 12));
	}
?>

<?php 
if (isset($_FILES['data_file']) && ($_POST['action'] == 'upload')) do_handle_upload(); 
if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'delete-file')) do_delete_file();
?>


<div class="left" style="margin-right: 3em;">
<?php show_server_files(); ?>
</div>



<div class="left" style="">
<?php show_process_status(); ?>

<p><button type="submit" name="action" value="bulk-load">Bulk Load</button> <button type="submit" name="action" value="load-abort">Stop Loading</button> <button type="submit" name="action" value="purge">Unload All</button></p>


<h2>Upload File</h2>

<p><input type="file" name="data_file" id="data_file" size="30"></p>

<p><button type="submit" name="action" value="upload">Upload File</button></p>

</div>

<div class="clear"></div>



<?php


	/* if the user has requested a load operation,
	include a call to the asyncronous loader here */
	if ( (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'bulk-load')) ||
	    (isset($_REQUEST['action']) && (substr($_REQUEST['action'], 0, 12) == 'file-toggle-')) )
	{
		invoke_asyncronous_loader();
	}
}



function invoke_asyncronous_loader()
{
// ! TODO:  To be sure, should really compute the full URL here ?
?><iframe src="?async-load" style="visibility: hidden; width: 650px; height: 50px; overflow-y: scroll; border: 1px solid red;"></iframe><?php
}





function show_server_files()
{
	$list = server_file_list();
	
?><table style="width: auto; min-width: 27em;">
<tr>
	<th>File</th>
	<th style="width: 7em;">Status</th>
	<th style="width: 1.5em;"></th>
	<th style=""></th>
</tr>
<?php
	foreach ($list as $file)
	{
		print '<tr>';
		print '<td>'. basename($file[1], '.aiml').'</td>';
		
		if ($file[2] == 'Loaded') $color = ' class="green"';
		else if ($file[2] == 'Load Error') $color = ' class="red"';
		else $color = '';
		
		print '<td'.$color.'>'.$file[2].'</td>';
		print '<td><a href="?page=import&action=delete-file&file='.$file[1].'">';
		JxWidget::small_delete_icon();
		print '</a></td>';
		
		print '<td>';
		if (substr($file[2], 0, 7) != 'Loading')
		{
			print '<button type="submit" name="action" value="file-toggle-'. $file[1] . '">';
			if ($file[2] == 'Loaded') print 'Unload';
			else print 'Load';
			print '</button>';
		}
		print '</td>';
		
		print '</tr>';
	}
?>
</table>
<?php
}


function show_process_status()
{
	$stmt = JxBotDB::$db->prepare('SELECT COUNT(*) FROM file WHERE last_update > DATE_SUB(NOW(), INTERVAL 20 SECOND)');
	$stmt->execute();
	$recent_status_changes = ($stmt->fetchAll(PDO::FETCH_NUM)[0][0] != 0);
	
?>

<h2>Status</h2>

<?php if ($recent_status_changes == false) { ?>
Idle.
<?php } else { ?>

<p style="margin-top: -20px; "><img src="<?php print JxBotConfig::bot_url(); ?>jxbot/core/gfx/43.GIF" style="vertical-align: middle; margin-right: 1.5em"> <?php

	$stmt = JxBotDB::$db->prepare('SELECT status,name FROM file WHERE status LIKE \'Loading%\'');
	$stmt->execute();
	$status = $stmt->fetchAll(PDO::FETCH_NUM);
	if (count($status) == 0) $status = 'Checking...';
	else $status = $status[0][0] . ', ' . $status[0][1];
	print $status;
	
?></p>

<script type="text/javascript">

// ! TODO:  Can be improved with an AJAX call to avoid reloading the page

window.setTimeout(function() {
	window.location = '?page=import';
}, 10000);

</script>
<?php
	}
}


