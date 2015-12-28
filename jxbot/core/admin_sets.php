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