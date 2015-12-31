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



if (isset($_POST['action']) && ($_POST['action'] == 'save'))
{
	JxBotConfig::set_option('bot_tz', $_POST['bot_tz']);
	//JxBotConfig::set_option('bot_active', $_POST['bot_active']);
	
	JxBotConfig::set_option('pre_strip_accents', $_POST['pre_strip_accents']);
	
	JxBotConfig::set_option('sys_cap_bot_ipm', $_POST['sys_cap_bot_ipm']);
	
	//JxBotConfig::set_option('admin_user', $_POST['admin_user']);
	JxBotConfig::set_option('admin_timeout', intval($_POST['admin_timeout']));
	
	//if (isset($_POST['bot_password']) && trim($_POST['bot_password']) !== '')
	//	JxBotConfig::set_option('admin_hash', hash('sha256', $_POST['bot_password']));
	
	JxBotConfig::save_configuration();
}



?>

<h2>Setup</h2>

<div class="field"><label for="bot_tz">Timezone: </label>
<?php JxBotConfig::widget_timezone(); ?></div>


<h2>Language Processing</h2>

<div class="field"><label for="pre_strip_accents">Strip Accents:</label>
<?php JxWidget::toggle_switch('pre_strip_accents', JxBotConfig::option('pre_strip_accents')); ?><br><small>(strip accents during normalisation; good for English)</small></div>


<h2>Security</h2>

<div class="field"><label for="sys_cap_bot_ipm">Bot Load Maximum: </label>
<input type="text" name="sys_cap_bot_ipm" id="sys_cap_bot_ipm" size="6" value="<?php print JxBotConfig::option('sys_cap_bot_ipm'); ?>"><br><small>(interactions per minute; 0 = unlimited)</small></div>

<div class="field"><label for="admin_timeout">Administration Timeout: </label>
<input type="text" name="admin_timeout" id="admin_timeout" size="6" value="<?php print JxBotConfig::option('admin_timeout'); ?>"><br><small>(minutes; 0 = no timeout)</small></div>


<p class="left" id="buttons"><button type="submit" name="action" value="save">Save</button></p>

