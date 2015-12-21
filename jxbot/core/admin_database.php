<?php


var_dump($_REQUEST);


//list($action) = JxBotUtil::inputs('action');


$page = (isset($_REQUEST['action']) ? $_REQUEST['action'] : 'lookup');
if ($page == 'sequences') page_sequences();
else if ($page == 'edit' && isset($_REQUEST['category'])) page_edit($_REQUEST['category']);
else if ($page == 'new-cat') do_new_category();
else if ($page == 'save-seq') do_save_seq();
else page_lookup();





function do_save_seq()
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

// move this to NL

function do_new_category()
{
	JxBotDB::$db->exec('INSERT INTO category VALUES (NULL)');
	$category_id = JxBotDB::$db->lastInsertId();
	
	page_edit($category_id);
}



function page_lookup()
{
?>

<h2>Lookup</h2>

<input type="hidden" name="action" value="sequences">

<?php JxWidget::textfield('input', 'Chat Input', '', 150); ?>

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

<h3>Matched</h3>

<?php 
$rows = NL::matching_sequences($inputs['input']);
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

<h3>Sequences</h3>

<?php 
$stmt = JxBotDB::$db->prepare('SELECT sequence_id,words FROM sequence WHERE category_id=?');
$stmt->execute(array($in_category_id));
$rows = $stmt->fetchAll(PDO::FETCH_NUM);
JxWidget::grid(array(
	array('label'=>'ID', 'id'=>0),
	array('label'=>'Sequence', 'id'=>1)
), $rows); 
?>

<p><?php JxWidget::textfield('new-seq', 'Sequence', '', 255); ?></p>

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

<p><?php JxWidget::button('Cancel', 'action', 'edit'); ?> <?php JxWidget::button('Add Sequence', 'action', 'save-seq'); ?></p>


<?php
}



