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



$inputs = JxBotUtil::inputs('action,map');
if ($inputs['action'] == 'new-map')
	do_new_map();
else if ($inputs['action'] == 'delete-map')
	do_delete_map();
	
else if ($inputs['action'] == 'new-item')
	do_new_item();
else if ($inputs['action'] == 'delete-item')
	do_delete_item();
	
else
{
	if ($inputs['map'] != '')
		list_items($inputs['map']);
	else
		list_maps();
}


function do_delete_item()
{
	$inputs = JxBotUtil::inputs('item');
	
	$stmt = JxBotDB::$db->prepare('SELECT map FROM map_item WHERE id=?');
	$stmt->execute(array($inputs['item']));
	$map = $stmt->fetchAll(PDO::FETCH_NUM);
	$map = $map[0][0];

	$inputs = JxBotUtil::inputs('item');
	$stmt = JxBotDB::$db->prepare('DELETE FROM map_item WHERE id=?');
	$stmt->execute(array($inputs['item']));
	
	list_items($map);
}


function do_new_item()
{
	$inputs = JxBotUtil::inputs('item-from,item-to,map');
	$item_from = $inputs['item-from'];
	$item_to = $inputs['item-to'];
	
	$stmt = JxBotDB::$db->prepare('INSERT INTO map_item (s_from, s_to, map) VALUES (?, ?, ?)');
	$stmt->execute(array($inputs['item-from'], $inputs['item-to'], $inputs['map']));
	
	list_items($inputs['map']);
}
	
	
function do_delete_map()
{
	$inputs = JxBotUtil::inputs('map');
	
	$stmt = JxBotDB::$db->prepare('DELETE FROM map_item WHERE id=?');
	$stmt->execute(array($inputs['map']));
	
	$stmt = JxBotDB::$db->prepare('DELETE FROM _map WHERE id=?');
	$stmt->execute(array($inputs['map']));
	
	list_maps();
}


function do_new_map()
{
	$inputs = JxBotUtil::inputs('map-name');
	$map_name = trim($inputs['map-name']);
	
	$stmt = JxBotDB::$db->prepare('INSERT INTO _map (name) VALUES (?)');
	$stmt->execute(array($map_name));
	
	list_items(JxBotDB::$db->lastInsertId());
}


function list_maps()
{
	$stmt = JxBotDB::$db->prepare('SELECT id,name from _map ORDER BY name');
	$stmt->execute();
	$maps = $stmt->fetchAll(PDO::FETCH_NUM);
	
	JxWidget::hidden('subpage', 'maps');
	
	JxWidget::grid(array(
		array('label'=>'ID', 'id'=>0, 'key'=>true, 'visible'=>false),
		array('label'=>'Map', 'id'=>1, 'link'=>'?page=database&subpage=maps&map=$$'),
		array('label'=>'Delete', 'id'=>':delete', 'link'=>'?page=database&subpage=maps&action=delete-map&map=$$')
	), $maps);
	
	print '<p><input type="text" size="30" name="map-name"> <button type="submit" name="action" value="new-map">New Map</button></p>';
}


function list_items($in_map)
{
	$stmt = JxBotDB::$db->prepare('SELECT name FROM _map WHERE id=?');
	$stmt->execute(array($in_map));
	$row = $stmt->fetchAll(PDO::FETCH_NUM);
	
	print '<h2>'.$row[0][0].'</h2>';
	
	JxWidget::hidden('subpage', 'maps');
	JxWidget::hidden('map', $in_map);
	
	$stmt = JxBotDB::$db->prepare('SELECT id,s_from,s_to FROM map_item WHERE map=? ORDER BY s_from');
	$stmt->execute(array($in_map));
	$items = $stmt->fetchAll(PDO::FETCH_NUM);
	JxWidget::grid(array(
		array('label'=>'ID', 'id'=>0, 'key'=>true, 'visible'=>false),
		array('label'=>'From', 'id'=>1, 'whitespace'=>'preserve'),
		array('label'=>'To', 'id'=>2, 'whitespace'=>'preserve'),
		array('label'=>'Delete', 'id'=>':delete', 'link'=>'?page=database&subpage=maps&action=delete-item&item=$$')
	), $items);
	
	print '<p><input type="text" size="30" name="item-from"> : <input type="text" size="30" name="item-to"> <button type="submit" name="action" value="new-item">Add Item</button></p>';
}


?>