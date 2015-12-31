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



$inputs = JxBotUtil::inputs('action,set');
if ($inputs['action'] == 'new-set')
	do_new_set();
else if ($inputs['action'] == 'delete-set')
	do_delete_set();
	
else if ($inputs['action'] == 'new-item')
	do_new_item();
else if ($inputs['action'] == 'delete-item')
	do_delete_item();
	
else
{
	if ($inputs['set'] != '')
		list_items($inputs['set']);
	else
		list_sets();
}


function do_delete_item()
{
	$inputs = JxBotUtil::inputs('item');
	
	$stmt = JxBotDB::$db->prepare('SELECT id FROM set_item WHERE phrase=?');
	$stmt->execute(array($inputs['item']));
	$set = $stmt->fetchAll(PDO::FETCH_NUM);
	$set = $set[0][0];

	$inputs = JxBotUtil::inputs('item');
	$stmt = JxBotDB::$db->prepare('DELETE FROM set_item WHERE phrase=?');
	$stmt->execute(array($inputs['item']));
	
	list_items($set);
}


function do_new_item()
{
	$inputs = JxBotUtil::inputs('item-phrase,set');
	$item_phrase = trim($inputs['item-phrase']);
	
	try
	{
		$stmt = JxBotDB::$db->prepare('INSERT INTO set_item (phrase, id) VALUES (?, ?)');
		$stmt->execute(array($inputs['item-phrase'], $inputs['set']));
	}
	catch (Exception $err) // just means we're trying to add something already existing usually
	{} 
	
	list_items($inputs['set']);
}
	
	
function do_delete_set()
{
	$inputs = JxBotUtil::inputs('set');
	
	$stmt = JxBotDB::$db->prepare('DELETE FROM set_item WHERE id=?');
	$stmt->execute(array($inputs['set']));
	
	$stmt = JxBotDB::$db->prepare('DELETE FROM _set WHERE id=?');
	$stmt->execute(array($inputs['set']));
	
	list_sets();
}


function do_new_set()
{
	$inputs = JxBotUtil::inputs('set-name');
	$set_name = trim($inputs['set-name']);
	
	$stmt = JxBotDB::$db->prepare('INSERT INTO _set (name) VALUES (?)');
	$stmt->execute(array($set_name));
	
	list_items(JxBotDB::$db->lastInsertId());
}


function list_sets()
{
	$stmt = JxBotDB::$db->prepare('SELECT id,name from _set ORDER BY name');
	$stmt->execute();
	$maps = $stmt->fetchAll(PDO::FETCH_NUM);
	
	JxWidget::hidden('subpage', 'sets');
	
	JxWidget::grid(array(
		array('label'=>'ID', 'id'=>0, 'key'=>true, 'visible'=>false),
		array('label'=>'Set', 'id'=>1, 'link'=>'?page=database&subpage=sets&set=$$'),
		array('label'=>'Delete', 'id'=>':delete', 'link'=>'?page=database&subpage=sets&action=delete-set&set=$$')
	), $maps);
	
	print '<p><input type="text" size="30" name="set-name"> <button type="submit" name="action" value="new-set">New Set</button></p>';
}


function list_items($in_set)
{
	$stmt = JxBotDB::$db->prepare('SELECT name FROM _set WHERE id=?');
	$stmt->execute(array($in_set));
	$row = $stmt->fetchAll(PDO::FETCH_NUM);
	
	print '<h2>'.$row[0][0].'</h2>';
	
	JxWidget::hidden('subpage', 'sets');
	JxWidget::hidden('set', $in_set);
	
	$stmt = JxBotDB::$db->prepare('SELECT id,phrase FROM set_item WHERE id=? ORDER BY phrase');
	$stmt->execute(array($in_set));
	$items = $stmt->fetchAll(PDO::FETCH_NUM);
	JxWidget::grid(array(
		array('label'=>'Phrase', 'id'=>1, 'key'=>true),
		array('label'=>'Delete', 'id'=>':delete', 'link'=>'?page=database&subpage=sets&action=delete-item&item=$$')
	), $items);
	
	print '<p><input type="text" size="30" name="item-phrase"> <button type="submit" name="action" value="new-item">Add Item</button></p>';
}


?>