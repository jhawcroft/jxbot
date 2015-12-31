<?php
/*******************************************************************************
JxBot - conversational agent for the web
Copyright (c) 2015 Joshua Hawcroft

    May all beings have happiness and the cause of happiness.
    May all beings be free of suffering and the cause of suffering.
    May all beings rejoice in the happiness of others.
    May all beings abide in equanimity; free of attachment and delusion.

Permission is hereby granted, free of charge, to any person obtaining a copy of 
this software and associated documentation files (the "Software"), to deal in 
the Software without restriction, including without limitation the rights to 
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so, 
subject to the following conditions:

The above copyright notice, preamble and this permission notice shall be 
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR 
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER 
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN 
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*******************************************************************************/

if (!defined('JXBOT_ADMIN')) die('Direct script access not permitted.');


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
		print '<td style="width: 1.5em;"><a href="?del-name='.$prop[0].'&page=chat&subpage=defaults">';
		JxWidget::small_delete_icon();
		print '</a></td>';
		print '</tr>';
	}
	print '</table>';
}

JxWidget::hidden('subpage', 'defaults');

?>

<div class="col-left"><?php editable_section($bot_properties, $rows_per_col); ?></div>
<div class="col-right"><?php editable_section($bot_properties, $rows_per_col); ?></div>
<div class="clear"></div>

<p>

<div class="field"><label for="new-name">New Default: </label>
<input type="text" id="new-name" name="new-name" size="30"> <input type="submit" value="Add"></div>
</p>


<p><input type="submit" value="Save"></p>
