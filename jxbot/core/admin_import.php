<?php

	
function do_handle_upload()
{
	if ($_FILES['data_file']['error'] === UPLOAD_ERR_OK)
	{
		$filename = $_FILES['data_file']['tmp_name'];
		$error = JxBotAiml::import($filename);
		if ($error === true)
			print '<p>AIML imported successfully.</p>';
		else
			print '<p>'.$error.'</p>';
	}
	else
		print '<p>Error uploading file.</p>';
}


function page_import_form()
{
?>

<?php if (isset($_FILES['data_file'])) do_handle_upload(); ?>

<p><label for="data_file">File:</label>
<input type="file" name="data_file" id="data_file" size="30"></p>

<p><?php JxWidget::button('Upload', 'upload', ''); ?></p>

<?php
}


page_import_form();




