<?php



//var_dump($_REQUEST);


//list($action) = JxBotUtil::inputs('action');

$inputs = JxBotUtil::inputs('action,category');
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : 'lookup');

if ($action == 'lookup') page_lookup();
else if ($action == 'new-cat') do_new_category();
else if ($action == 'edit') page_edit($inputs['category']);
else if ($action == 'del-cat') do_delete_category();
else if ($action == 'add-tmpl') do_add_tmpl();
else if ($action == 'del-tmpl') do_del_tmpl();
else if ($action == 'edit-tmpl') page_edit_tmpl();
else if ($action == 'save-tmpl') do_save_tmpl();

/*

$page = 
if ($page == 'sequences' && isset($_REQUEST['input']) && (trim($_REQUEST['input']) != '')) page_sequences();
else if ($page == 'edit' && isset($_REQUEST['category'])) page_edit($_REQUEST['category']);
else if ($page == 'new-cat') do_new_category();
else if ($page == 'add-seq') do_add_seq();
else if ($page == 'del-seq') do_del_seq();
else if ($page == 'add-tmpl') do_add_tmpl();
else if ($page == 'del-tmpl') do_del_tmpl();
else if ($page == 'edit-tmpl' && isset($_REQUEST['template'])) page_edit_tmpl($_REQUEST['template']);
else if ($page == 'save-tmpl') do_save_tmpl();
else page_lookup();*/



function do_add_tmpl()
{
	$inputs = JxBotUtil::inputs('category,new-tmpl');
	JxBotEngine::template_add(intval($inputs['category']), $inputs['new-tmpl']);
	page_edit($inputs['category']);
}


function do_del_tmpl()
{
	$inputs = JxBotUtil::inputs('template');
	page_edit( JxBotEngine::template_delete($inputs['template']) );
}


function do_save_tmpl()
{
	$inputs = JxBotUtil::inputs('tmpl-id,template');
	$category_id = JxBotEngine::template_update($inputs['tmpl-id'], $inputs['template']);
	page_edit($category_id);
}



function do_add_seq()
{
	$inputs = JxBotUtil::inputs('category,new-seq,override');
	
	if (!$inputs['override'])
	{
		/* check if there are any existing sequences that match the proposed new sequence */
		$existing = NL::exact_sequence_exists($inputs['new-seq']);
		if ($existing !== false)
		{
			/* list the matches with a warning and override question */
			page_confirm_new_seq($existing);
			return;
		}
	}

	/* go ahead and add the new sequence */
	NL::register_sequence(intval($inputs['category']), $inputs['new-seq']);
	page_edit($inputs['category']);
}


function do_del_seq()
{
	$inputs = JxBotUtil::inputs('seq-id');
	$category_id = NL::kill_sequence($inputs['seq-id']);
	page_edit($category_id);
}




function do_new_category()
{
	$category_id = JxBotEngine::category_new();
	page_edit($category_id);
}


function do_delete_category()
{
	$inputs = JxBotUtil::inputs('category');
	JxBotEngine::category_delete($inputs['category']) ;
	page_lookup();
}



function page_lookup()
{
?>

<h2>Lookup</h2>

<input type="hidden" name="action" value="sequences">

<?php JxWidget::textfield(array(
	'name'=>'input',
	'label'=>'Chat Input',
	'max'=>150,
	'autofocus'=>true
)); ?>

<p>
<?php JxWidget::button('Lookup Input'); ?>
</p>

<?php
}


function page_sequences()
{
	$inputs = JxBotUtil::inputs('input');
	
	//$stmt = JxBotDB::$db->prepare('SELECT sequence_id,words FROM
	
?>

<h2>Sequences</h2>

<p>For input: <strong><?php print $inputs['input']; ?></strong></p>
<input type="hidden" name="input" value="<?php print $inputs['input']; ?>">

<?php 
$rows = NL::matching_sequences($inputs['input']);

print '<h3>Matched</h3>';
JxWidget::grid(array(
	array('label'=>'ID', 'id'=>0, 'key'=>true, 'visible'=>false),
	array('label'=>'Sequence', 'id'=>2, 'link'=>'?page=database&action=edit&category=$$')
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



function page_edit($in_category_id)
{
?>

<input type="hidden" name="category" value="<?php print $in_category_id; ?>">


<h2>Edit Category</h2>

<h3>Patterns</h3>

<?php 
$stmt = JxBotDB::$db->prepare('SELECT id,value FROM pattern WHERE category=?');
$stmt->execute(array($in_category_id));
$rows = $stmt->fetchAll(PDO::FETCH_NUM);
JxWidget::grid(array(
	array('label'=>'ID', 'id'=>0, 'visible'=>false, 'key'=>true),
	array('label'=>'Sequence', 'id'=>1),
	array('label'=>'Delete', 'id'=>':delete', 'link'=>'?page=database&action=del-pat&pat-id=$$')
), $rows); 
?>

<p><?php JxWidget::textfield(array(
	'name'=>'new-pat', 
	'label'=>'New Pattern', 
	'max'=>255
)); ?></p>

<p><?php JxWidget::button('Add Pattern', 'action', 'add-pat'); ?></p>



<h3>Templates</h3>

<?php 
$rows = JxBotEngine::fetch_templates($in_category_id);
JxWidget::grid(array(
	array('label'=>'Template', 'id'=>1, 'link'=>'?page=database&action=edit-tmpl&template=$$'),
	array('label'=>'ID', 'id'=>0, 'visible'=>false, 'key'=>true),
	array('label'=>'Delete', 'id'=>':delete', 'link'=>'?page=database&action=del-tmpl&template=$$')
), $rows); 
?>

<p><?php JxWidget::memofield('new-tmpl', 'New Template', '', 5, true); ?></p>

<p><?php JxWidget::button('Add Template', 'action', 'add-tmpl'); ?></p>



<p><?php JxWidget::button('Lookup Another', 'action', 'lookup'); ?> <?php JxWidget::button('Delete Category', 'action', 'del-cat'); ?></p>


<?php
}



function page_confirm_new_seq(&$in_existing_seq)
{
	$inputs = JxBotUtil::inputs('category,new-seq');
?>

<?php 
JxWidget::hidden($inputs, 'category,new-seq');
JxWidget::hidden('override', 1);
?>

<h2>Confirm New Sequence</h2>

<p>There are already one or more sequences in the database that exactly match the proposed new sequence.</p>

<?php 
JxWidget::grid(array(
	array('label'=>'Category', 'id'=>0),
	array('label'=>'Sequence', 'id'=>2)
), $in_existing_seq); 
?>


<p>Are you sure you want to add this sequence?</p>

<p><?php JxWidget::button('Cancel', 'action', 'edit'); ?> <?php JxWidget::button('Add Sequence', 'action', 'add-seq'); ?></p>


<?php
}



function page_edit_tmpl()
{
	$inputs = JxBotUtil::inputs('template');
	$template = JxBotEngine::template_fetch($inputs['template']);

?>

<?php 
JxWidget::hidden('category', $template['category']);
JxWidget::hidden('tmpl-id', $template['id']);
?>

<h2>Edit Template</h2>

<p><?php JxWidget::memofield('template', 'Template', $template['template'], 5, true); ?></p>


<p><?php JxWidget::button('Cancel', 'action', 'edit'); ?> <?php JxWidget::button('Save Template', 'action', 'save-tmpl'); ?></p>

<?php
}


