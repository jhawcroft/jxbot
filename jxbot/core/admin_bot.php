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
	array('General', '?page=bot', 'subpage', ''),
	array('Predicates', '?page=bot&subpage=pred', 'subpage', 'pred')
));

$subpage = JxBotUtil::inputs('subpage');
if ($subpage['subpage'] == 'pred')
	require_once('admin_bot_pred.php');
else
{
	

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



<p><input type="submit" value="Save"></p>


<?php
}
