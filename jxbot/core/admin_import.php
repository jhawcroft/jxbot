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


	
function do_handle_upload()
{
	if ($_FILES['data_file']['error'] === UPLOAD_ERR_OK)
	{
		$filename = $_FILES['data_file']['tmp_name'];
		$importer = new JxBotAimlImport();
		$result = $importer->import($filename);
		if (is_array($result))
		{
			print '<p>AIML imported successfully.';
			foreach ($result as $notice)
			{
				print '<br>'.htmlentities($notice);
			}
			print '</p>';
		}
		else // error
			print '<p>'.$result.'</p>';
	}
	else
		print '<p>Error uploading file.</p>';
}


function page_import_form()
{
?>

<h2>Bulk Reload</h2>

<?php

if (isset($_POST['action']) && ($_POST['action'] == 'bulk-reload'))
	bulk_reload();

?>

<p><button type="submit" name="action" value="bulk-reload">Purge & Reload</button></p>



<h2>Individual File</h2>

<?php if (isset($_FILES['data_file']) && ($_POST['action'] == 'upload')) do_handle_upload(); ?>

<p><label for="data_file">File:</label>
<input type="file" name="data_file" id="data_file" size="50"></p>

<p><button type="submit" name="action" value="upload">Upload File</button></p>



<h2>Purge</h2>

<p><button type="submit" name="action" value="purge">Purge Categories</button></p>



<?php
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


