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

/* 'category manager'; storage, import, export and editing of the category database
of natural language patterns and output templates */

class JxBotNLData
{

	public static function category_new($in_that, $in_topic)
	{
	//print 'CATEGORY: '.$in_topic.'<br>';
	//return;
	
		$stmt = JxBotDB::$db->prepare('INSERT INTO category (that, topic) VALUES (?, ?)');
		$stmt->execute(array($in_that, $in_topic));
		$category_id = JxBotDB::$db->lastInsertId();
		return $category_id;
	}
	
	
	public static function category_update($in_category_id, $in_that, $in_topic)
	{
		$stmt = JxBotDB::$db->prepare('UPDATE category SET that=?, topic=? WHERE id=?');
		$stmt->execute(array($in_that, $in_topic, $in_category_id));
		
		// this actually needs to do more - because strictly speaking
		// it should remove all patterns and recreate with modified that & topic values  **
		// in the meantime, can simply prevent category updates in the UI
	}
	
	
	public static function category_delete($in_category_id)
	{
		$stmt = JxBotDB::$db->prepare('DELETE FROM category WHERE id=?');
		$stmt->execute(array($in_category_id));
	}
	
	
	public static function category_fetch($in_category_id)
	{
		$stmt = JxBotDB::$db->prepare('SELECT that,topic FROM category WHERE id=?');
		$stmt->execute(array($in_category_id));
		$row = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $row[0];
	}
	
	
	private static function make_sort_key(&$in_term)
	/* @high		Boolean.  If true, word has the highest matching priority.
					(AIML 2.0)
	*/
	{
		/* wildcards: */
		if ($in_term == '#') return 1;
		else if ($in_term == '_') return 2;
		else if ($in_term == '^') return 8;
		else if ($in_term == '*') return 9;
		
		/* bot property: */
		else if (substr($in_term, 0, 1) == ':') 
		{
			$in_term = substr($in_term, 1);
			return 6;
		}
		
		/* set name: */
		else if (substr($in_term, strlen($in_term)-1, 1) == ':') 
		{
			$in_term = substr($in_term, 0, strlen($in_term)-1);
			return 6;
		}
		
		/* high priority word: */
		else if (substr($in_term, 0, 1) == '$')
		{
			$in_term = substr($in_term, 1);
			return 0;
		}
		
		/* normal word: */
		else return 5;
	}
	
	
	public static function pattern_add($in_category_id, $in_text, $in_topic, $in_that)
	{
	//print 'PATTERN: '.$in_text.' : '.$in_that.' : '.$in_topic.'<br>';
	//return;
		$in_full = $in_text . ' : ' . $in_that . ' : ' . $in_topic;
		$in_full = JxBotAiml::translate_pattern_tags($in_full);
		$terms = JxBotNL::normalise_pattern($in_full);
		$in_full = JxBotNL::upper($in_full);
		
		$count = count($terms);
		$last_node = NULL; /* root of graph */
		
		for ($index = 0; $index < $count; $index++)
		{
			$expression = $terms[$index];
			
			if ($last_node === NULL)
			{
				$stmt = JxBotDB::$db->prepare('SELECT id FROM pattern_node WHERE parent IS NULL AND expression=?');
				$stmt->execute(array($expression));
			}
			else
			{
				$stmt = JxBotDB::$db->prepare('SELECT id FROM pattern_node WHERE parent=? AND expression=?');
				$stmt->execute(array($last_node, $expression));
			}
			$existing = $stmt->fetchAll(PDO::FETCH_NUM);
			if (count($existing) == 0)
			{
				$stmt = JxBotDB::$db->prepare('INSERT INTO pattern_node (parent, expression, sort_key, is_terminal)
											   VALUES (?, ?, ?, ?)');
				$sort_key = JxBotNLData::make_sort_key($expression);
				$stmt->execute(array(
					$last_node, 
					$expression, 
					$sort_key, 
					(($index + 1 >= $count) ? 1 : 0)
					));
				$last_node = JxBotDB::$db->lastInsertId();
			}
			else
			{
				$last_node = $existing[0][0];
				if ($index + 1 >= $count)
				{
					$stmt = JxBotDB::$db->prepare('UPDATE pattern_node SET is_terminal=1 WHERE id=?');
					$stmt->execute(array($last_node));
				}
			}
		}
		
		$stmt = JxBotDB::$db->prepare('INSERT INTO pattern (id, category, value, that, topic) VALUES (?, ?, ?, ?, ?)');
		$stmt->execute(array($last_node, $in_category_id, $in_text, $in_that, $in_topic));
		
		return $last_node;
	}
	
	
	public static function pattern_delete($in_pattern_id)
	{
		$stmt = JxBotDB::$db->prepare('SELECT category FROM pattern WHERE id=?');
		$stmt->execute(array($in_pattern_id));
		$row = $stmt->fetchAll(PDO::FETCH_NUM);
		if (count($row) == 0) return NULL;
		
		$stmt = JxBotDB::$db->prepare('DELETE FROM pattern WHERE id=?');
		$stmt->execute(array($in_pattern_id));
		
		$stmt = JxBotDB::$db->prepare('UPDATE pattern_node SET is_terminal=0 WHERE id=?');
		$stmt->execute(array($in_pattern_id));
		
		return $row[0][0];
	}
	
	
	public static function fetch_patterns($in_category_id)
	{
		$stmt = JxBotDB::$db->prepare('SELECT id,value,that,topic FROM pattern WHERE category=?');
		$stmt->execute(array($in_category_id));
		$rows = $stmt->fetchAll(PDO::FETCH_NUM);
		return $rows;
	}
	
	
	
	public static function template_add($in_category_id, $in_template)
	{
	//print 'TEMPLATE: '.$in_template.'<br>';
	//return;
	
		$stmt = JxBotDB::$db->prepare('INSERT INTO template (category, template) VALUES (?, ?)');
		$stmt->execute(array($in_category_id, $in_template));
	}
	
	
	public static function template_update($in_template_id, $in_text)
	{
		$stmt = JxBotDB::$db->prepare('UPDATE template SET template=? WHERE id=?');
		$stmt->execute(array($in_text, $in_template_id));
		
		$stmt = JxBotDB::$db->prepare('SELECT category FROM template WHERE id=?');
		$stmt->execute(array($in_template_id));
		$row = $stmt->fetchAll(PDO::FETCH_NUM);
		return $row[0][0];
	}
	
	
	
	
	public static function template_delete($in_template_id)
	{
		$stmt = JxBotDB::$db->prepare('SELECT category FROM template WHERE id=?');
		$stmt->execute(array($in_template_id));
		$row = $stmt->fetchAll(PDO::FETCH_NUM);
		if (count($row) == 0) return NULL;
		
		$stmt = JxBotDB::$db->prepare('DELETE FROM template WHERE id=?');
		$stmt->execute(array($in_template_id));
		
		return $row[0][0];
	}
	
	
	public static function fetch_templates($in_category_id)
	{
		$stmt = JxBotDB::$db->prepare('SELECT id,template FROM template WHERE category=?');
		$stmt->execute(array($in_category_id));
		$rows = $stmt->fetchAll(PDO::FETCH_NUM);
		return $rows;
	}
	
	
	
	
	
	
	public static function template_fetch($in_template_id)
	{
		$stmt = JxBotDB::$db->prepare('SELECT id,template,category FROM template WHERE id=?');
		$stmt->execute(array($in_template_id));
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $rows[0];
	}
	
	
}


