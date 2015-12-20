<?php

if (isset($_POST['bot_name']))
{
	BotDefaults::set_option('bot_name', $_POST['bot_name']);
	BotDefaults::set_option('bot_tz', $_POST['bot_tz']);
	BotDefaults::set_option('bot_active', (isset($_POST['bot_active']) ? 1 : 0));
	
	if (isset($_POST['bot_password']))
		BotDefaults::set_option('admin_hash', hash('sha256', $_POST['bot_password']));
	
	BotDefaults::save_configuration();
}

?>

<p class="field"><label for="bot_name">Bot Name: </label>
<input type="text" name="bot_name" id="bot_name" size="40" value="<?php print BotDefaults::option('bot_name'); ?>"></p>

<p class="field"><label for="bot_tz">Timezone: </label>
<select name="bot_tz" id="bot_tz" class="focusable">
<option value=""></option>
<?php
$timezone_identifiers = DateTimeZone::listIdentifiers();
foreach ($timezone_identifiers as $tz)
{
	print '<option value="'.$tz.'" '.(BotDefaults::option('bot_tz') == $tz ? ' selected="true"' : '').'>'.$tz.'</option>';
}
?>
</select></p>



<p class="field"><label for="bot_name">Active:</label>
<?php
JxWidget::toggle_switch('bot_active', BotDefaults::option('bot_active'));
?></p>


<p class="field"><label for="bot_password">Change Password: </label>
<input type="text" name="bot_password" id="bot_password" size="20"></p>



<p class="left" id="buttons"><input type="submit" value="Save" class="blue"></p>