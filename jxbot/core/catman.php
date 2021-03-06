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

/* 'category manager'; storage, import, export and editing of the category database
of natural language patterns and output templates */

if (!defined('JXBOT')) die('Direct script access not permitted.');


class JxBotNLData
{


	public static function set_file_status($in_name, $in_status)
	{
		try
		{
			$stmt = JxBotDB::$db->prepare('INSERT INTO file (name, status) VALUES (?, ?)');
			$stmt->execute(array( $in_name, $in_status ));
		}
		catch (Exception $err) {} // already in the table
		
		$stmt = JxBotDB::$db->prepare('UPDATE file SET status=? WHERE name=?');
		$stmt->execute(array( $in_status, $in_name ));
	}
	

/********************************************************************************
Dictionary
*/
// eventually can be extended to provide definitions and thesaurus functionality
// loaded from external CSV files, as well as word counts
// dictionary only grows at present - unless explicitly maintained by user

	public static function word_add_lookup($in_word)
	/* looks up a word in the dictionary and adds it if it doesn't exist;
	returns only the word ID */
	{
		$in_word = JxBotNL::upper(trim($in_word));
		
		$stmt = JxBotDB::$db->prepare('SELECT id FROM word WHERE word=?');
		$stmt->execute(array($in_word));
		$id = $stmt->fetchAll(PDO::FETCH_NUM);
		if (count($id) == 0)
		{
			$stmt = JxBotDB::$db->prepare('INSERT INTO word (word) VALUES (?)');
			$stmt->execute(array($in_word));
			$id = JxBotDB::$db->lastInsertId();
		}
		else $id = $id[0][0];
		
		return $id;
	}
	
	
	public static function word_count()
	{
		$stmt = JxBotDB::$db->prepare('SELECT COUNT(*) FROM word');
		$stmt->execute();
		$count = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
		return $count;
	}
	
	

/********************************************************************************
Category Management
*/

	public static function purge_categories()
	{
		JxBotDB::$db->exec('
		delete from pattern_node;
		delete from pattern;
		delete from template;
		delete from category;
		update file set status=\'Not Loaded\';
		');
	}


	public static function category_new($in_that, $in_topic)
	/* creates a new category with the specified <that> and <topic>;
	returns the new category ID */
	{
	//print 'CATEGORY: '.$in_topic.'<br>';
	//return;
	
		$stmt = JxBotDB::$db->prepare('INSERT INTO category (that, topic) VALUES (?, ?)');
		$stmt->execute(array($in_that, $in_topic));
		$category_id = JxBotDB::$db->lastInsertId();
		return $category_id;
	}
	
	
	public static function category_update($in_category_id, $in_that, $in_topic)
	/* updates the <that> and <topic> of the specified category */
	{
		/* check inputs and apply defaults */
		if (trim($in_that) == '') $in_that = '*';
		if (trim($in_topic) == '') $in_topic = '*';
	
		/* update the main category record */
		$stmt = JxBotDB::$db->prepare('UPDATE category SET that=?, topic=? WHERE id=?');
		$stmt->execute(array($in_that, $in_topic, $in_category_id));
		
		/* ! NOTE:  unlike a standard AIML interpreter, JxBot allows multiple patterns 
		for each category - see file ENGINEERING for more information */
		
		/* re-add all the patterns, whose that & topic values will have changed;
		this is not efficient for AIML load but necessary for some admin operations,
		thus AIML load should collate patterns, that and topic and only add at completion
		of a category. */
		$stmt = JxBotDB::$db->prepare('SELECT that,topic FROM category WHERE id=?');
		$stmt->execute(array($in_category_id));
		$row = $stmt->fetchAll(PDO::FETCH_NUM)[0];
		$that = $row[0];
		$topic = $row[1];
		
		$stmt = JxBotDB::$db->prepare('SELECT id,value FROM pattern WHERE category=?');
		$stmt->execute(array($in_category_id));
		$patterns = $stmt->fetchAll(PDO::FETCH_NUM);
		foreach ($patterns as $row)
		{
			$text = $row[1];
			$pattern = $row[0];
			
			/* remove the pattern */
			JxBotNLData::pattern_delete($pattern);
			
			/* reinsert the pattern */
			JxBotNLData::pattern_add($in_category_id, $text, $that, $topic);
		}
		
		return $in_category_id;
	}
	
	
	public static function category_delete($in_category_id)
	/* deletes a category from the database */
	{
		$stmt = JxBotDB::$db->prepare('DELETE FROM category WHERE id=?');
		$stmt->execute(array($in_category_id)); 
		
		// to do - cleanup other data! - 
		// if we were using foreign keys and InnoDB this would be easier ***
	}
	
	
	public static function category_fetch($in_category_id)
	/* fetches a category by ID for editing within the administration interface */
	{
		$stmt = JxBotDB::$db->prepare('SELECT that,topic FROM category WHERE id=?');
		$stmt->execute(array($in_category_id));
		$row = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $row[0];
	}
	

/********************************************************************************
Pattern Management
*/

	public static function pattern_count()
	/* returns the total number of unique patterns within the system */
	{
		$stmt = JxBotDB::$db->prepare('SELECT COUNT(*) FROM pattern');
		$stmt->execute();
		$count = $stmt->fetchAll(PDO::FETCH_NUM);
		return $count[0][0];
	}
	

	private static function make_sort_key(&$in_term)
	/* computes an internal 'sort key' for the given term expression;
	and modifies the term as required to simplify matching */
	{
		/* match part separator */
		if ($in_term == ':') return 5;
		
		/* wildcards: */
		if ($in_term == '#') return 1;
		else if ($in_term == '_') return 2;
		else if ($in_term == '^') return 8;
		else if ($in_term == '*') return 9;
		
		/* bot property: */
		else if (substr($in_term, 0, 1) == ':') 
		{
			//$in_term = substr($in_term, 1);
			// ! Note:  May modify term in future, if the match algorithm is 
			//          adjusted accordingly to use the sort key for term identification
			return 6;
		}
		
		/* set name: */
		else if (substr($in_term, strlen($in_term)-1, 1) == ':') 
		{
			//$in_term = substr($in_term, 0, strlen($in_term)-1);
			// ! Note:  May modify term in future, if the match algorithm is 
			//          adjusted accordingly to use the sort key for term identification
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
	
	
	public static function pattern_add($in_category_id, $in_text, $in_that, $in_topic)
	/* adds a pattern to a category, with the supplied text, that and topic;
	returns the pattern ID */
	{
	//print 'PATTERN: '.$in_text.' : '.$in_that.' : '.$in_topic.'<br>';
	//return;
		$in_full = $in_text . ' : ' . $in_that . ' : ' . $in_topic;
		$translator = new JxBotAimlPattern();
		$in_full = $translator->translate($in_full);
		//$in_full = JxBotAiml::translate_pattern_tags($in_full);
		$terms = JxBotNL::normalise_pattern($in_full);
		$in_full = JxBotNL::upper($in_full);
		
		$count = count($terms);
		$last_node = 0; /* root of graph */
		
		for ($index = 0; $index < $count; $index++)
		{
			$expression = $terms[$index];
			
			/*if ($last_node === NULL)
			{
				$stmt = JxBotDB::$db->prepare('SELECT id FROM pattern_node WHERE parent IS NULL AND expression=?');
				$stmt->execute(array($expression));
			}
			else
			{*/
			$stmt = JxBotDB::$db->prepare('SELECT id FROM pattern_node WHERE parent=? AND expression=?');
			$stmt->execute(array($last_node, $expression));
			//}
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
				
				if ( (($sort_key == 5) || ($sort_key == 0)) && ($expression != ':') 
					&& (!is_numeric($expression)) && (trim($expression) != ''))
				{
					JxBotNLData::word_add_lookup($expression);
				}
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
		
		try
		{
			$stmt = JxBotDB::$db->prepare('
				INSERT INTO pattern (id, category, value, that, topic, term_count) 
				VALUES (?, ?, ?, ?, ?, ?)');
			$stmt->execute(array( $last_node, $in_category_id, $in_text, 
				$in_that, $in_topic, count($terms) - 2 ));
		}
		catch (Exception $err)
		{
			/* detected duplicate pattern */
			// for now, just ignore
			throw new Exception('Pattern already exists.');
			//print 'Duplicate pattern: '.$in_full.'<br>';
		}
		
		return $last_node;
	}
	
	
	public static function pattern_delete($in_pattern_id)
	/* deletes a pattern from a category; returns the category ID */
	{
		$stmt = JxBotDB::$db->prepare('SELECT category FROM pattern WHERE id=?');
		$stmt->execute(array($in_pattern_id));
		$row = $stmt->fetchAll(PDO::FETCH_NUM);
		if (count($row) == 0) return NULL;
		$category = $row[0][0];
		
		$stmt = JxBotDB::$db->prepare('DELETE FROM pattern WHERE id=?');
		$stmt->execute(array($in_pattern_id));
		
		/* cleanup the tree */
		$parent = 0;
		for ($node = $in_pattern_id; $node != 0; $node = $parent)
		{
			/* only delete the node if it has no children */
			$stmt = JxBotDB::$db->prepare('SELECT COUNT(*) FROM pattern_node WHERE parent=?');
			$stmt->execute(array($node));
			$count = intval( $stmt->fetchAll(PDO::FETCH_NUM)[0][0] );
			if ($count > 0) break;
			
			/* grab the node's parent prior to deletion */
			$stmt = JxBotDB::$db->prepare('SELECT parent FROM pattern_node WHERE id=?');
			$stmt->execute(array($node));
			$parent = intval( $stmt->fetchAll(PDO::FETCH_NUM)[0][0] );
			
			/* delete the node */
			$stmt = JxBotDB::$db->prepare('DELETE FROM pattern_node WHERE id=?');
			$stmt->execute(array($node));
			//print 'Deleted node #'.$node.'<br>';
		}
		
		return $category;
	}
	
	
	public static function fetch_patterns($in_category_id)
	/* retrieves a list of patterns suitable for display within bot administration */
	{
		$stmt = JxBotDB::$db->prepare('SELECT id,value,that,topic FROM pattern WHERE category=?');
		$stmt->execute(array($in_category_id));
		$rows = $stmt->fetchAll(PDO::FETCH_NUM);
		return $rows;
	}
	
	
/********************************************************************************
Template Management
*/
	public static function template_add($in_category_id, $in_template)
	/* adds a template to the specified category */
	{
	//print 'TEMPLATE: '.$in_template.'<br>';
	//return;
	
		$stmt = JxBotDB::$db->prepare('INSERT INTO template (category, template) VALUES (?, ?)');
		$stmt->execute(array($in_category_id, $in_template));
	}
	
	
	public static function template_update($in_template_id, $in_text)
	/* modifies an existing template; returns the owning category ID */
	{
		$stmt = JxBotDB::$db->prepare('UPDATE template SET template=? WHERE id=?');
		$stmt->execute(array($in_text, $in_template_id));
		
		$stmt = JxBotDB::$db->prepare('SELECT category FROM template WHERE id=?');
		$stmt->execute(array($in_template_id));
		$row = $stmt->fetchAll(PDO::FETCH_NUM);
		return $row[0][0];
	}
	
	
	public static function template_delete($in_template_id)
	/* deletes a specified template from a category; returns the owning category ID */
	{
		$stmt = JxBotDB::$db->prepare('SELECT category FROM template WHERE id=?');
		$stmt->execute(array($in_template_id));
		$row = $stmt->fetchAll(PDO::FETCH_NUM);
		if (count($row) == 0) return NULL;
		
		$stmt = JxBotDB::$db->prepare('DELETE FROM template WHERE id=?');
		$stmt->execute(array($in_template_id));
		
		return $row[0][0];
	}
	
	
	public static function template_fetch($in_template_id)
	/* retrieves a template for editing within the administration interface */
	{
		$stmt = JxBotDB::$db->prepare('SELECT id,template,category FROM template WHERE id=?');
		$stmt->execute(array($in_template_id));
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $rows[0];
	}
	
	
	public static function fetch_templates($in_category_id)
	/* retrieves a list of templates either for selection by the output generator,
	or for listing within the administration interface */
	{
		$stmt = JxBotDB::$db->prepare('SELECT id,template FROM template WHERE category=?');
		$stmt->execute(array($in_category_id));
		$rows = $stmt->fetchAll(PDO::FETCH_NUM);
		return $rows;
	}
	
	
	
/********************************************************************************
Sets
*/

	public static function set_values($in_set_name)
	{
		$stmt = JxBotDB::$db->prepare('
			SELECT phrase FROM set_item JOIN _set ON set_item.id=_set.id WHERE _set.name=? ORDER BY phrase');
		$stmt->execute(array( trim($in_set_name) ));
		$values = array();
		$rows = $stmt->fetchAll(PDO::FETCH_NUM);
		foreach ($rows as $row)
		{
			$values[] = JxBotNL::normalise($row[0]);
		}
		return $values;
	}
	
}


