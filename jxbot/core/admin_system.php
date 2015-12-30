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