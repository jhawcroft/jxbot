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


class JxBotEngine
{
	public static function normalise($in_input, $in_keep_wildcards = false)
	{
		$output = strip_accents(mb_strtoupper($in_input));
		
		$punctuation = array(',', '!', '?', '\'');
		if (!$in_keep_wildcards) $punctuation = array_merge($punctuation,
			array('*'));
  		$output = str_replace($punctuation, '', $output);
  		
  		$whitespace = array("\t", "\n", "\r");
  		$output = str_replace($whitespace, ' ', $output);
  		
  		$output = explode(' ', $output);
  		
  		$output = array_diff($output, array(''));
  		
		return $output;
	}
	
	
	public static function category_new()
	{
		JxBotDB::$db->exec('INSERT INTO category VALUES (NULL)');
		$category_id = JxBotDB::$db->lastInsertId();
		return $category_id;
	}
	
	
	public static function category_delete($in_category_id)
	{
		$stmt = JxBotDB::$db->prepare('DELETE FROM category WHERE category_id=?');
		$stmt->exec(array($in_category_id));
	}
	
	
	private static function make_sort_key($in_term)
	{
	// needs checking
		if ($in_term == '*') return 9;
		else if ($in_term == '_') return 1;
		else return 5;
	}
	
	
	public static function pattern_add($in_category_id, $in_text)
	{
		$terms = JxBotEngine::normalise($in_text, true);
		$count = count($terms);
		$last_node = NULL; /* root of graph */
		for ($index = 0; $index < $count; $index++)
		{
			$expression = $terms[$index];
			
			$stmt = JxBotDB::$db->prepare('SELECT id FROM pattern_node WHERE parent=? AND expression=?');
			$stmt->execute(array($last_node, $expression));
			$existing = $stmt->fetchAll(PDO::FETCH_NUM);
			if (count($existing) == 0)
			{
				$stmt = JxBotDB::$db->prepare('INSERT INTO pattern_node (parent, expression, sort_key, is_terminal)
											   VALUES (?, ?, ?, ?)');
				$stmt->execute(array(
					$last_node, 
					$expression, 
					JxBotEngine::make_sort_key($expression), 
					($index + 1 >= $count)
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
		
		$stmt = JxBotDB::$db->prepare('INSERT INTO pattern (id, category, value)');
		$stmt->execute(array($last_node, $in_category_id, $in_text));
		
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
	
	
	public static function template_add($in_category_id, $in_template)
	{
		$stmt = JxBotDB::$db->prepare('INSERT INTO template (category_id, template) VALUES (?, ?)');
		$stmt->execute(array($in_category_id, $in_template));
	}
	
	
	public static function template_update($in_template_id, $in_text)
	{
		$stmt = JxBotDB::$db->prepare('UPDATE template SET template=? WHERE id=?');
		$stmt->execute(array($in_template_id, $in_text));
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
}





