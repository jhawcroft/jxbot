<?php

if (isset($_POST['new-name']) && trim($_POST['new-name']) !== '')
	JxBotConfig::def_add_pred($_POST['new-name'], '');

if (isset($_REQUEST['del-name']) && trim($_REQUEST['del-name']) !== '')
{
	JxBotConfig::def_delete_pred($_REQUEST['del-name']);
}



if (isset($_POST['save']))
{
	foreach ($_POST as $key => $value)
	{
		if (substr($key, 0, 4) == 'def_')
			JxBotConfig::set_option($key, $value);
	}
	
	JxBotConfig::save_configuration();
}


JxWidget::hidden('save', 1);

?>


<?php
$bot_properties = JxBotConfig::client_defaults();
$rows_per_col = ceil(count($bot_properties) / 2.0);

function editable_section(&$properties, $row_count)
{
	if (count($properties) == 0) return;
	print '<table>';
	for ($i = 0; $i < $row_count; $i++)
	{
		$prop = array_shift($properties);
		if ($prop === null) break;
		print '<tr>';
		print '<td style="width: 10em;">'.$prop[1].'</td>';
		print '<td><input type="text" name="'.$prop[0].'" size="20" value="'.$prop[2].'" style="width:95%"></td>';
		print '<td style="width: 1.5em;"><a href="?del-name='.$prop[0].'&page=client">';
		JxWidget::small_delete_icon();
		print '</a></td>';
		print '</tr>';
	}
	print '</table>';
}

?>

<div class="col-left"><?php editable_section($bot_properties, $rows_per_col); ?></div>
<div class="col-right"><?php editable_section($bot_properties, $rows_per_col); ?></div>
<div class="clear"></div>

<p>

<div class="field"><label for="new-name">New Default: </label>
<input type="text" id="new-name" name="new-name" size="30"> <input type="submit" value="Add"></div>
</p>


<p><input type="submit" value="Save"></p>
