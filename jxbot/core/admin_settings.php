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



if (isset($_POST['bot_tz']))
{
	JxBotConfig::set_option('bot_tz', $_POST['bot_tz']);
	JxBotConfig::set_option('bot_active', $_POST['bot_active']);
	
	JxBotConfig::set_option('pre_strip_accents', $_POST['pre_strip_accents']);
	
	JxBotConfig::set_option('sys_cap_bot_ipm', $_POST['sys_cap_bot_ipm']);
	
	JxBotConfig::set_option('admin_user', $_POST['admin_user']);
	JxBotConfig::set_option('admin_timeout', intval($_POST['admin_timeout']));
	
	if (isset($_POST['bot_password']) && trim($_POST['bot_password']) !== '')
		JxBotConfig::set_option('admin_hash', hash('sha256', $_POST['bot_password']));
	
	JxBotConfig::save_configuration();
}

?>


<h2>General</h2>

<div class="field"><label for="bot_active">Online:</label>
<?php JxWidget::toggle_switch('bot_active', JxBotConfig::option('bot_active')); ?></div>

<div class="field"><label for="bot_tz">Timezone: </label>
<?php JxBotConfig::widget_timezone(); ?></div>


<h2>Language Processing</h2>

<div class="field"><label for="pre_strip_accents">Strip Accents:</label>
<?php JxWidget::toggle_switch('pre_strip_accents', JxBotConfig::option('pre_strip_accents')); ?><br><small>(strip accents during normalisation; good for English)</small></div>


<h2>Security</h2>

<div class="field"><label for="sys_cap_bot_ipm">Bot Load Maximum: </label>
<input type="text" name="sys_cap_bot_ipm" id="sys_cap_bot_ipm" size="6" value="<?php print JxBotConfig::option('sys_cap_bot_ipm'); ?>"><br><small>(interactions per minute; 0 = unlimited)</small></div>

<div class="field"><label for="admin_user">Administration Username: </label>
<input type="text" name="admin_user" id="admin_user" size="20" value="<?php print JxBotConfig::option('admin_user'); ?>"></div>

<div class="field"><label for="bot_password">Change Password: </label>
<input type="text" name="bot_password" id="bot_password" size="20"></div>

<div class="field"><label for="admin_timeout">Administration Timeout: </label>
<input type="text" name="admin_timeout" id="admin_timeout" size="6" value="<?php print JxBotConfig::option('admin_timeout'); ?>"><br><small>(minutes; 0 = no timeout)</small></div>



<p class="left" id="buttons"><input type="submit" value="Save" class="blue"></p>