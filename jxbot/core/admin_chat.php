<?php


?>


<?php JxWidget::textfield(array(
	'name'=>'input', 
	'label'=>'Chat Input',
	'max'=>150,
	'autofocus'=>true
)); ?>

<p>
<?php JxWidget::button('Talk'); ?>
</p>


<p><img src="<?php print JxBotConfig::bot_url(); ?>jxbot/core/gfx/robot-small.png"></p>

<?php

$inputs = JxBotUtil::inputs('input');
if (trim($inputs['input']) != '') 
{
	Converse::resume_conversation('admin');
	$response = Converse::get_response($inputs['input']);
}
else $response = Converse::get_greeting();

	print '<p>Bot:</p>';
	print '<blockquote>';
	print $response;
	print '</blockquote>';


?>



<!--
<hr>

<-- STATS GO HERE ->

<h2>Status</h2>

-->