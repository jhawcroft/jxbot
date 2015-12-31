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
	array('Talk', '?page=chat', 'subpage', ''),
	array('Defaults', '?page=chat&subpage=defaults', 'subpage', 'defaults'),
	array('Logs', '?page=chat&subpage=logs', 'subpage', 'logs'),
));

$subpage = JxBotUtil::inputs('subpage');
if ($subpage['subpage'] == 'defaults')
	require_once('admin_client.php');

else if ($subpage['subpage'] == 'logs')
	require_once('admin_logs.php');

else
{


$inputs = JxBotUtil::inputs('input');
if (trim($inputs['input']) != '') 
{
	JxBotConverse::resume_conversation('admin');
	
	$response = JxBotConverse::get_response($inputs['input']);
}
else $response = JxBotConverse::get_greeting();


?>


<?php JxWidget::textfield(array(
	'name'=>'input', 
	'label'=>'Administrator',
	'max'=>150,
	'autofocus'=>true
)); ?>


<p><?php JxWidget::button('Talk'); ?></p>


<div class="left">
<img src="<?php print JxBotConfig::bot_url(); ?>jxbot/core/gfx/robot-small.png" id="chat-robot">
</div>


<div class="bubble" style="max-width: 80%;">
<div class="bubble-top"><div class="bubble-corner-tl"></div><div class="bubble-corner-tr"></div></div>
<div class="bubble-left"></div>
<div class="bubble-content">

<?php print $response; ?>

</div>
<div class="bubble-right"></div>
<div class="bubble-bot"><div class="bubble-corner-bl"></div><div class="bubble-corner-br"></div></div>
</div>



<?php
/*
print '<pre>'.JxBotConverse::history_request(0).'</pre>'; // should be this input
print '<pre>'.JxBotConverse::history_request(1).'</pre>';

print '<pre>'.JxBotConverse::history_response(0).'</pre>'; // should be this response now that it's invoked
print '<pre>'.JxBotConverse::history_response(1).'</pre>';

*/
}
?>

