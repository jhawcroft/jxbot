<?php



if (isset($_POST['bot_tz']))
{
	JxBotConfig::set_option('bot_tz', $_POST['bot_tz']);
	JxBotConfig::set_option('bot_active', $_POST['bot_active']);
	
	if (isset($_POST['bot_password']) && trim($_POST['bot_password']) !== '')
		JxBotConfig::set_option('admin_hash', hash('sha256', $_POST['bot_password']));
	
	JxBotConfig::save_configuration();
}

?>


<div class="field"><label for="bot_name">Active:</label>
<?php JxWidget::toggle_switch('bot_active', JxBotConfig::option('bot_active')); ?></div>


<div class="field"><label for="bot_tz">Timezone: </label>
<?php JxBotConfig::widget_timezone(); ?></div>


<div class="field"><label for="bot_password">Change Password: </label>
<input type="text" name="bot_password" id="bot_password" size="20"></div>



<p class="left" id="buttons"><input type="submit" value="Save" class="blue"></p>