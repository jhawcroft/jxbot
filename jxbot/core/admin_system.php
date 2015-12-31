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


JxWidget::tabs(array(
	array('General', '?page=system', 'subpage', ''),
	array('Settings', '?page=system&subpage=opts', 'subpage', 'opts'),
	array('Log', '?page=system&subpage=logs', 'subpage', 'logs'),
	array('About', '?page=system&subpage=about', 'subpage', 'about'),
));

$subpage = JxBotUtil::inputs('subpage');
if ($subpage['subpage'] == 'logs')
	require_once('admin_sys_log.php'); 

else if ($subpage['subpage'] == 'about')
	require_once('admin_about.php');
	
else if ($subpage['subpage'] == 'opts')
	require_once('admin_sys_opts.php');

else
{




if (isset($_POST['action']) && ($_POST['action'] == 'save'))
{
	//JxBotConfig::set_option('bot_tz', $_POST['bot_tz']);
	JxBotConfig::set_option('bot_active', $_POST['bot_active']);
	
	//JxBotConfig::set_option('pre_strip_accents', $_POST['pre_strip_accents']);
	
	//JxBotConfig::set_option('sys_cap_bot_ipm', $_POST['sys_cap_bot_ipm']);
	
	JxBotConfig::set_option('admin_user', $_POST['admin_user']);
	//JxBotConfig::set_option('admin_timeout', intval($_POST['admin_timeout']));
	
	if (isset($_POST['bot_password']) && trim($_POST['bot_password']) !== '')
		JxBotConfig::set_option('admin_hash', hash('sha256', $_POST['bot_password']));
	
	JxBotConfig::save_configuration();
}


?>


<div class="field"><label for="bot_active">Online:</label>
<?php JxWidget::toggle_switch('bot_active', JxBotConfig::option('bot_active')); ?></div>


<div class="field"><label for="admin_user">Administration Username: </label>
<input type="text" name="admin_user" id="admin_user" size="20" value="<?php print JxBotConfig::option('admin_user'); ?>"></div>

<div class="field"><label for="bot_password">Change Password: </label>
<input type="text" name="bot_password" id="bot_password" size="20"></div>



<p class="left" id="buttons"><button type="submit" name="action" value="save">Save</button></p>



<?php
}
?>