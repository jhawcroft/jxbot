<?php


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


<div class="bubble">
<div class="bubble-top"><div class="bubble-corner-tl"></div><div class="bubble-corner-tr"></div></div>
<div class="bubble-left"></div>
<div class="bubble-content">

<?php print $response; ?>

</div>
<div class="bubble-right"></div>
<div class="bubble-bot"><div class="bubble-corner-bl"></div><div class="bubble-corner-br"></div></div>
</div>


<!--
<hr>

<-- STATS GO HERE ->

<h2>Status</h2>

-->