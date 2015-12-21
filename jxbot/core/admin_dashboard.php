<?php


//print Converse::get_response("Hi there!!  How \taré you?");


?>


<h2>Chat</h2>

<?php JxWidget::textfield(array(
	'name'=>'input', 
	'label'=>'Chat Input',
	'max'=>150
)); ?>

<p>
<?php JxWidget::button('Talk'); ?>
</p>

<p><!-- RESPONSE GOES HERE --></p>



<!--
<hr>

<-- STATS GO HERE ->

<h2>Status</h2>

-->