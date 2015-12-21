<?php


$page = (isset($_POST['action']) ? $_POST['action'] : 'lookup');
if ($page == 'sequences') page_sequences();
else if ($page == 'edit') page_edit();
else if ($page == 'new-cat') do_new_category();
else page_lookup();



function do_new_category()
{
	
	page_edit();
}



function page_lookup()
{
?>

<h2>Lookup</h2>

<input type="hidden" name="action" value="sequences">

<?php JxWidget::textfield('input', 'Chat Input', '', 150); ?>

<p>
<?php JxWidget::button('Lookup'); ?>
</p>

<?php
}


function page_sequences()
{
	$inputs = JxBotUtil::inputs('input');
	
?>

<h2>Sequences</h2>

<p>For input: <strong><?php print $inputs['input']; ?></strong></p>
<input type="hidden" name="input" value="<?php print $inputs['input']; ?>">

<h3>Matched</h3>

<?php 
$rows = array();
JxWidget::grid(array(
	array('label'=>'Sequence')
), $rows); 
?>


<h3>Similar</h3>

<?php 
$rows = array();
JxWidget::grid(array(
	array('label'=>'Sequence')
), $rows); 
?>


<p><?php JxWidget::button('Lookup Another', 'action', 'lookup'); ?> <?php JxWidget::button('New Category', 'action', 'new-cat'); ?></p>


<?php
}



function page_edit()
{
?>

<h2>Edit Category</h2>

<h3>Sequences</h3>

<?php 
$rows = array();
JxWidget::grid(array(
	array('label'=>'Sequence')
), $rows); 
?>

<p><?php JxWidget::textfield('edit-seq', 'Sequence', '', 255); ?></p>

<p><?php JxWidget::button('Save Sequence', 'action', 'save-seq'); ?></p>



<h3>Templates</h3>

<?php 
$rows = array();
JxWidget::grid(array(
	array('label'=>'Template')
), $rows); 
?>

<p><?php JxWidget::memofield('edit-tmpl', 'Template', '', 5, true); ?></p>

<p><?php JxWidget::button('Save Template', 'action', 'save-templ'); ?></p>



<p><?php JxWidget::button('Lookup Another', 'action', 'lookup'); ?> <?php JxWidget::button('Delete Category', 'action', 'del-cat'); ?></p>


<?php
}


