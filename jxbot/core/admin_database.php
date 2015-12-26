<?php
/********************************************************************************
 *  JxBot - conversational agent for the web
 *  Copyright (c) 2015 Joshua Hawcroft
 *
 *      May all beings have happiness and the cause of happiness.
 *      May all beings be free of suffering and the cause of suffering.
 *      May all beings rejoice in the happiness of others.
 *      May all beings abide in equanimity; free of attachment and delusion.
 * 
 *  Redistribution and use in source and binary forms, with or without
 *  modification, are permitted provided that the following conditions are met:
 *
 *  1) Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *  2) Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in the
 *     documentation and/or other materials provided with the distribution.
 *
 *  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 *  ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 *  WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 *  DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS BE LIABLE FOR ANY
 *  DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 *  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 *  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 *  ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 *  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 *  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *******************************************************************************/

if (!defined('JXBOT_ADMIN')) die('Direct script access not permitted.');


//var_dump($_REQUEST);


//list($action) = JxBotUtil::inputs('action');

$inputs = JxBotUtil::inputs('action,category');
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');

if ($action == '') page_lookup();
else if ($action == 'lookup') page_lookup_results();

else if ($action == 'new-cat') do_new_category();
else if ($action == 'edit') page_edit($inputs['category']);
else if ($action == 'del-cat') do_delete_category();
else if ($action == 'save-ctx') do_update_category();

else if ($action == 'add-tmpl') do_add_tmpl();
else if ($action == 'del-tmpl') do_del_tmpl();
else if ($action == 'edit-tmpl') page_edit_tmpl();
else if ($action == 'save-tmpl') do_save_tmpl();

else if ($action == 'add-pat') do_add_pat();
else if ($action == 'del-pat') do_del_pat();




function do_add_tmpl()
{
	$inputs = JxBotUtil::inputs('category,new-tmpl');
	JxBotNLData::template_add(intval($inputs['category']), $inputs['new-tmpl']);
	page_edit($inputs['category']);
}


function do_del_tmpl()
{
	$inputs = JxBotUtil::inputs('template');
	page_edit( JxBotNLData::template_delete($inputs['template']) );
}


function do_save_tmpl()
{
	$inputs = JxBotUtil::inputs('tmpl-id,template');
	$category_id = JxBotNLData::template_update($inputs['tmpl-id'], $inputs['template']);
	page_edit($category_id);
}


function do_add_pat()
{
	$inputs = JxBotUtil::inputs('category,new-pat,topic,that,override');
	
	if (!$inputs['override'])
	{
		/* check if there are any existing patterns that match the proposed new pattern */
		 // todo
	}
	
	/* go ahead and add the new pattern */
	JxBotNLData::pattern_add($inputs['category'], $inputs['new-pat'], $inputs['that'], $inputs['topic']);
	page_edit($inputs['category']);
}


function do_del_pat()
{
	$inputs = JxBotUtil::inputs('pat-id');
	$category_id = JxBotNLData::pattern_delete($inputs['pat-id']);
	page_edit($category_id);
}




function do_new_category()
{
	$inputs = JxBotUtil::inputs('that,topic');
	$category_id = JxBotNLData::category_new($inputs['that'], $inputs['topic']);
	page_edit($category_id);
}


function do_update_category()
{
	$inputs = JxBotUtil::inputs('category,that,topic');
	JxBotNLData::category_update($inputs['category'], $inputs['that'], $inputs['topic']);
	page_edit($inputs['category']);
}


function do_delete_category()
{
	$inputs = JxBotUtil::inputs('category');
	JxBotNLData::category_delete($inputs['category']) ;
	page_lookup();
}



function page_lookup()
{
	$inputs = JxBotUtil::inputs('input,that,topic');
?>

<h2>Lookup</h2>

<input type="hidden" name="action" value="lookup">

<?php JxWidget::textfield(array(
	'name'=>'topic',
	'label'=>'Topic',
	'max'=>150,
	'autofocus'=>true,
	'value'=>$inputs['topic']
)); ?>

<?php JxWidget::textfield(array(
	'name'=>'that',
	'label'=>'That',
	'max'=>150,
	'value'=>$inputs['that']
)); ?>

<?php JxWidget::textfield(array(
	'name'=>'input',
	'label'=>'Input',
	'max'=>150,
	'value'=>$inputs['input']
)); ?>

<p>
<?php JxWidget::button('Lookup Input'); ?>
</p>

<?php
}


function page_lookup_results()
{
	$inputs = JxBotUtil::inputs('input,that,topic');
	
	if (trim($inputs['that']) === '')
		$inputs['that'] = '*';
	if (trim($inputs['topic']) === '')
		$inputs['topic'] = '*';
	
JxWidget::hidden($inputs, 'input,that,topic');
?>

<h2>Categories</h2>

<p>For input: <strong><?php print $inputs['input']; ?> : <?php print $inputs['that']; ?> : <?php print $inputs['topic']; ?></strong></p>

<?php 
$category_id = JxBotEngine::match($inputs['input'], $inputs['that'], $inputs['topic']);
if ($category_id === false) {  ?>
<h3>No Exact Match</h3>
<?php } else { 
	print '<h3>Exact Match</h3>';
	
	$patterns = JxBotNLData::fetch_patterns($category_id);
	JxWidget::grid(array(
		array('label'=>'ID', 'id'=>0, 'key'=>true, 'visible'=>false),
		array('label'=>'Pattern', 'id'=>1, 'link'=>'?page=database&action=edit&category='.$category_id, 'encode'=>true)
	), $patterns); 
}
?>


<!--<h3>Similar</h3>-->


<p><?php JxWidget::button('Lookup Another', 'action', ''); ?> <?php JxWidget::button('New Category', 'action', 'new-cat'); ?></p>


<?php
}



function page_edit($in_category_id)
{
	$inputs = JxBotUtil::inputs('input');
	
	$category = JxBotNLData::category_fetch($in_category_id);
?>

<input type="hidden" name="category" value="<?php print $in_category_id; ?>">


<h2>Edit Category</h2>


<h3>Context</h3>

<p><?php JxWidget::textfield(array(
	'name'=>'topic',
	'label'=>'Topic',
	'max'=>255,
	'value'=>$category['topic']
)); ?></p>

<p><?php JxWidget::textfield(array(
	'name'=>'that',
	'label'=>'That',
	'max'=>255,
	'value'=>$category['that']
)); ?></p>

<p><?php JxWidget::button('Update Context', 'action', 'save-ctx'); ?></p>


<h3>Patterns</h3>

<?php 
$stmt = JxBotDB::$db->prepare('SELECT id,value FROM pattern WHERE category=?');
$stmt->execute(array($in_category_id));
$rows = $stmt->fetchAll(PDO::FETCH_NUM);
JxWidget::grid(array(
	array('label'=>'ID', 'id'=>0, 'visible'=>false, 'key'=>true),
	array('label'=>'Pattern', 'id'=>1, 'encode'=>true),
	array('label'=>'Delete', 'id'=>':delete', 'link'=>'?page=database&action=del-pat&pat-id=$$')
), $rows); 
?>

<p><?php JxWidget::textfield(array(
	'name'=>'new-pat', 
	'label'=>'New Pattern', 
	'max'=>255,
	'value'=>$inputs['input']
)); ?></p>

<p><?php JxWidget::button('Add Pattern', 'action', 'add-pat'); ?></p>



<h3>Templates</h3>

<?php 
$rows = JxBotNLData::fetch_templates($in_category_id);
JxWidget::grid(array(
	array('label'=>'Template', 'id'=>1, 'link'=>'?page=database&action=edit-tmpl&template=$$', 'encode'=>true),
	array('label'=>'ID', 'id'=>0, 'visible'=>false, 'key'=>true),
	array('label'=>'Delete', 'id'=>':delete', 'link'=>'?page=database&action=del-tmpl&template=$$')
), $rows); 
?>

<p><?php JxWidget::memofield('new-tmpl', 'New Template', '', 5, true); ?></p>

<p><?php JxWidget::button('Add Template', 'action', 'add-tmpl'); ?></p>



<p><?php JxWidget::button('Lookup Another', 'action', ''); ?> <?php JxWidget::button('Delete Category', 'action', 'del-cat'); ?></p>


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
	$template = JxBotNLData::template_fetch($inputs['template']);

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


