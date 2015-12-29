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
	array('Settings', '?page=bot', 'subpage', '')
));



if (isset($_POST['new-name']) && trim($_POST['new-name']) !== '')
	JxBotConfig::bot_add_prop($_POST['new-name'], '');

if (isset($_REQUEST['del-name']) && trim($_REQUEST['del-name']) !== '')
{
	JxBotConfig::bot_delete_prop($_REQUEST['del-name']);
}



if (isset($_POST['bot_name']))
{
	//JxBotConfig::set_option('bot_name', $_POST['bot_name']);
	foreach ($_POST as $key => $value)
	{
		if (substr($key, 0, 4) == 'bot_')
			JxBotConfig::set_option($key, $value);
	}
	
	JxBotConfig::save_configuration();
}


?>

<p><div class="field"><label for="bot_name">Bot Name: </label>
<input type="text" name="bot_name" id="bot_name" size="40" value="<?php print JxBotConfig::option('bot_name'); ?>"></div></p>

<div class="field"><label for="bot_birthday">Birthday: </label>
<input type="text" name="bot_birthday" id="bot_birthday" size="12" value="<?php print JxBotConfig::option('bot_birthday'); ?>"> (YYYY/MM/DD)</div>

<!-- consider putting birthday here and automatically calculating age -->


<?php
$bot_properties = JxBotConfig::bot_properties();
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
		print '<td style="width: 1.5em;"><a href="?del-name='.$prop[0].'&page=bot">';
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

<div class="field"><label for="new-name">New Property: </label>
<input type="text" id="new-name" name="new-name" size="30"> <input type="submit" value="Add"></div>
</p>


<p><input type="submit" value="Save"></p>
