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

	protected $input_terms = null;
	protected $term_index = 0;
	protected $term_limit = 0;
	protected $wild_values = null;
	protected $wild_that_values = null;
	protected $wild_topic_values = null;
	protected $unwind_stage = 0;
	
	
	public static function upper($in_input)
	{
		return mb_strtoupper($in_input);
	}
	

	public static function normalise($in_input, $in_keep_wildcards = false)
	{
		$output = strip_accents(mb_strtoupper($in_input));
		
		if (!$in_keep_wildcards)
		{
			$punctuation = array(',', '!', '?', '\'', '*');
			$output = str_replace($punctuation, '', $output);
  		}
  		
  		$whitespace = array("\t", "\n", "\r");
  		$output = str_replace($whitespace, ' ', $output);
  		
  		$output = explode(' ', $output);
  		
  		$output = array_values( array_diff($output, array('')) );
  		
		return $output;
	}
	
	
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
	
	
	private static function make_sort_key($in_term, $in_high = false)
	/* @high		Boolean.  If true, word has the highest matching priority.
					(AIML 2.0)
	*/
	{
		if ($in_term == '#') return 1;
		else if ($in_term == '_') return 2;
		else if ($in_term == '^') return 8;
		else if ($in_term == '*') return 9;
		else return ($in_high ? 0 : 5);
	}
	
	
	public static function pattern_add($in_category_id, $in_text, $in_topic, $in_that)
	{
	//print 'PATTERN: '.$in_text.' : '.$in_that.' : '.$in_topic.'<br>';
	//return;
		$in_full = $in_text . ' : ' . $in_that . ' : ' . $in_topic;
		$in_full = JxBotAiml::translate_pattern_tags($in_full);
		$terms = JxBotEngine::normalise($in_full, true);
		$in_full = JxBotEngine::upper($in_full);
		
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
				$stmt->execute(array(
					$last_node, 
					$expression, 
					JxBotEngine::make_sort_key($expression), 
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
	
	
	public static function template_fetch($in_template_id)
	{
		$stmt = JxBotDB::$db->prepare('SELECT id,template,category FROM template WHERE id=?');
		$stmt->execute(array($in_template_id));
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $rows[0];
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
	
	
	public static function fetch_patterns($in_category_id)
	{
		$stmt = JxBotDB::$db->prepare('SELECT id,value,that,topic FROM pattern WHERE category=?');
		$stmt->execute(array($in_category_id));
		$rows = $stmt->fetchAll(PDO::FETCH_NUM);
		return $rows;
	}
	
	
	
	private static function is_wildcard($in_expr)
	{
		return (($in_expr == '*' || $in_expr == '_' || 
			$in_expr == '^' || $in_expr == '#'
			|| substr($in_expr, 0, 1) == '@'));
	}
	
	// example:
	
	// input:  HELLO SWEETIE HOW ARE YOU
	
	// patterns:
	// HELLO SWEETIE
	// HELLO SWEETIE HOW AM I
	// HELLO SWEETIE * ARE THEY
	
	// needs to search the longer patterns first!
	
	// ideally accumulate wildcard values on way back up when returning true
	
	private function accumulate_wild_values(&$in_values)
	{
		if ($this->unwind_stage == 2)
			$this->wild_topic_values[] = $in_values;
		else if ($this->unwind_stage == 1)
			$this->wild_that_values[] = $in_values;
		else
			$this->wild_values[] = $in_values;
	}
	
	
	private static function is_zero_wildcard($in_expr)
	{
		return ($in_expr === '^' || $in_expr === '#');
	}
	
	
	protected function get_term($in_term_index)
	{
		if ($in_term_index < $this->term_limit)
			return $this->input_terms[$in_term_index];
		else
			return  ':';
	}
	
	
	protected function walk($in_parent_id, $in_term_index)
	/* takes inputs as arrays, will probably call recursively;
	best to avoid passing by reference where possible - maybe use an object context
	as PHP's implementation of by reference is 'very strange' indeed */
	{
		$current_term = $this->get_term($in_term_index);
		
		//print "Walk  parent=$in_parent_id, term_index=$in_term_index, term=$current_term<br>";
		
		/* look in this branch for all possible matching subbranches */
		// might want to add a wild flag, and possibly a set table for AIML v2 with a set ID 
		if ($in_parent_id === null)
		{ // consider using zero 0 to eliminate this double-handling... **
			$stmt = JxBotDB::$db->prepare("SELECT id,expression,is_terminal FROM pattern_node 
				WHERE parent IS NULL AND expression IN (?, '*', '_') ORDER BY sort_key");
			$stmt->execute(array($current_term));
		}
		else
		{
			$stmt = JxBotDB::$db->prepare("SELECT id,expression,is_terminal FROM pattern_node 
				WHERE parent=? AND expression IN (?, '*', '_') ORDER BY sort_key");
			$stmt->execute(array($in_parent_id, $current_term));
		}
		$possible_branches = $stmt->fetchAll(PDO::FETCH_NUM);
		
		//print '<pre>';
		//var_dump($possible_branches);
		//print '</pre>';
		
		foreach ($possible_branches as $possibility)
		{
			/* decode the possibility and prepare to match */
			list($br_parent, $br_expr, $br_terminal) = $possibility;
			$is_wildcard = JxBotEngine::is_wildcard($br_expr);
			
			//print "Considering possible branch=$br_parent, expr=$br_expr, term=$br_terminal, wild=$is_wildcard  :<br>";
			
			if (!$is_wildcard)
			/* if the node isn't a wildcard, we need no special matching algorithm */
			{
				//print 'trying to match term against word...<br>';
				
				if ($in_term_index + 1 < $this->term_limit)
				{
					/* match remaining input to subbranch */
					$match = $this->walk($br_parent, $in_term_index + 1);
					if ($match !== false) 
					{
						if ($br_expr === ':') $this->unwind_stage--;
						return $match;
					}
				}
				else if ($br_terminal)
				{
					//print "Ran out of input @ terminal; matched $br_parent<br>";
					
					/* ran out of input; match if terminal node */
					if ($br_expr === ':') $this->unwind_stage--;
					return $br_parent;
				}
				/* otherwise, match failed */
				//print 'failed<br>';
			}
			else
			/* node is a wildcard; match one or more terms */
			{
				//print 'trying to match terms against wildcard...<br>';
				
				/* prepare to match wildcard */
				$wildcard_terms = array();
				$term_index = $in_term_index;
				$term = $current_term;
				$zero_or_more = JxBotEngine::is_zero_wildcard($br_expr);
				
				/* always match the first term for a 1+ wildcard;
				wildcards never match : */
				if (!$zero_or_more && $term != ':')
				{
					//print 'accumulate first 1+ term<br>';
					$wildcard_terms[] = $term;
					$term = $this->get_term(++ $term_index);
				}
				
				/* match as few terms as possible
				to this wildcard */
				while ($term_index < $this->term_limit)
				{			
					/* try match the current subbranch;
					effectively looking for the end of the wildcard processing */
					$matched = $this->walk($br_parent, $term_index);
					if ($matched !== false)
					/* subbranch matched; this branch matched */
					{
						$this->accumulate_wild_values($wildcard_terms);
						if ($br_expr === ':') $this->unwind_stage--;
						return $matched;
					}
					
					/* match the term to this wildcard */
					$wildcard_terms[] = $term;
					$term = $this->get_term(++ $term_index);
				}
			
				//print 'wildcard ran out of input<br>';
			
				/* inspect the wildcard match to see if matching
				should continue, or if we should fail here */
				$failed = (!$zero_or_more && count($wildcard_terms) == 0);
				if (!$failed)
				{				
					/* if submatching didn't run till the end,
					then this wildcard must be terminal */
					if ($br_terminal) 
					{
						$this->accumulate_wild_values($wildcard_terms);
						if ($br_expr === ':') $this->unwind_stage--;
						return $br_parent;
					}
				}
				
				/* run out of input on non-terminal; match failed */
			}
		}
		
		/* no possible subbranches; no match this branch */
		return false;
	}
	
	
	public static function match($in_input, $in_that, $in_topic)
	/* takes inputs in normal string form, returns some kind of array of information
	about the match (if any); assumes matching a single sentence (splitting already done) */
	{
		if ($in_that == '*' || $in_that == '') $in_that = 'undefined';
		if ($in_topic == '*' || $in_topic == '') $in_topic = 'undefined';
	
		$search_terms = JxBotEngine::normalise($in_input);
		$search_terms[] = ':';
		$search_terms = array_merge($search_terms, JxBotEngine::normalise($in_that));
		$search_terms[] = ':';
		$search_terms = array_merge($search_terms, JxBotEngine::normalise($in_topic));
		
		$context = new JxBotEngine();
		$context->input_terms = $search_terms;
		$context->term_index = 0;
		$context->term_limit = count($search_terms);
		$context->wild_values = array();
		$context->wild_that_values = array();
		$context->wild_topic_values = array();
		$context->unwind_stage = 2;

		//var_dump($search_terms);
		//print '<br>';

		$matched_pattern = $context->walk(NULL, 0);
		if ($matched_pattern === false) return false;
		
		//print 'Matched pattern: '.$matched_pattern.'<br>';
		
		/*print '<p>Wildcard values:<pre>';
		var_dump($context->wild_values);
		print '</pre></p>';
		
		print '<p>Wildcard THAT values:<pre>';
		var_dump($context->wild_that_values);
		print '</pre></p>';
		
		print '<p>Wildcard TOPIC values:<pre>';
		var_dump($context->wild_topic_values);
		print '</pre></p>';*/
		
		$stmt = JxBotDB::$db->prepare('SELECT category FROM pattern WHERE id=?');
		$stmt->execute(array($matched_pattern));
		$category = $stmt->fetchAll(PDO::FETCH_NUM);
		$category = $category[0][0];
		
		return $category;
	}
	
}





