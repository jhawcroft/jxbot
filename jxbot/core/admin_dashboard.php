<?php


?>


<h2>Chat</h2>

<?php JxWidget::textfield(array(
	'name'=>'input', 
	'label'=>'Chat Input',
	'max'=>150,
	'autofocus'=>true
)); ?>

<p>
<?php JxWidget::button('Talk'); ?>
</p>


<?php

$inputs = JxBotUtil::inputs('input');
if (trim($inputs['input']) != '')
{
	print '<p>Bot:</p>';
	print '<blockquote>';
	print Converse::get_response($inputs['input']);
	print '</blockquote>';
}

?>



<!--
<hr>

<-- STATS GO HERE ->

<h2>Status</h2>

-->