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

/* core logic; responsible for finding the most appropriate category to handle
any given user input */

if (!defined('JXBOT')) die('Direct script access not permitted.');


class JxBotEngine
{
	/* track of a few things during the matching process: */
	
	protected $input_terms = null;       /* the normalised input as an array of terms */
	protected $term_limit = 0;           /* the number of terms (^ above) */
	
	/* each wildcard array has an element for each corresponding wildcard;
	   each element in turn consists of an ordered array of matching terms */
	protected $wild_values = null;       /* input that matches wildcard(s) or
	                                        <set>...</set> */
	protected $wild_that_values = null;  /* prior input that matches a wildcard in the
                                            <that> clause of the matching category */
	protected $wild_topic_values = null; /* portion of the topic predicate that matches
	                                        a wildcard in the <topic> clause of the
	                                        matching category */
	protected $unwind_stage = 0;         /* once a match is found, tracks the unwinding
	                                        of the recursive call stack to determine
	                                        which of the three wildcard value arrays
	                                        (^ above) in which to store wildcard values */
	                                        
	protected $matched_category_id = 0;  /* the ID of the matching category */
	
	
	private function accumulate_wild_values(&$in_values)
	/* registers the specified value in the appropriate array for later use in
	the output template (eg. AIML <star/> value) */
	{
		/*  ** these arrays may be back-to-front when multiple values exist ?
		probably need to insert at the front not the end TODO */
	
		$in_value = implode(' ', $in_values);
		if ($this->unwind_stage == 2)
			$this->wild_topic_values[] = $in_value;
		else if ($this->unwind_stage == 1)
			$this->wild_that_values[] = $in_value;
		else
			$this->wild_values[] = $in_value;
	}
	
	
	private static function is_wildcard($in_expr)
	/* returns true if the internal pattern expression is a wildcard */
	{
		return ($in_expr == '*' || $in_expr == '_' || 
				$in_expr == '^' || $in_expr == '#');
	}
	
	
	private static function is_zero_wildcard($in_expr)
	/* returns true if the internal pattern expression is a wildcard that matches
	zero or more words */
	{
		return ($in_expr === '^' || $in_expr === '#');
	}
	
	
	private static function is_set_ref($in_expr)
	/* returns the name of a set, if the supplied internal pattern expression is a set
	name, ie. has a trailing colon :
	otherwise returns false */
	{
		if ($in_expr == ':') return false;
		if (substr($in_expr, strlen($in_expr) - 1, 1) == ':')
			return substr($in_expr, 0, strlen($in_expr) - 1);
		else
			return false;
	}
	
	
	private static function is_bot_ref($in_expr)
	/* returns the name of a bot property, if the supplied internal pattern expression 
	is a bot property reference, ie. has a leading colon :
	otherwise returns false */
	{
		if ($in_expr == ':') return false;
		if (substr($in_expr, 0, 1) == ':')
			return substr($in_expr, 1);
		else
			return false;
	}
	

	protected function get_term($in_term_index)
	/* returns the current term from the user input, or the colon : if the end of the
	input is reached so that matching fails */
	{
		if ($in_term_index < $this->term_limit)
			return $this->input_terms[$in_term_index];
		else
			return  '.'; /* end of input */
	}
	
	
	protected function try_match_values($in_branch, &$in_values, $in_save_match, $in_term_index, $is_terminal)
	/* takes a set of multi-term values and attempts to find a match with the remainder
	of the input; can optionally save the matching input (eg. an AIML <star/> value) */
	{
		$current_term = $this->get_term($in_term_index);
		
		/* iterate over set of supplied possibilities */
		foreach ($in_values as $trial_value)
		{
			//print 'Trying '.implode(' ', $trial_value).'<br>';
			//var_dump($current_term);
			
			/* does this possibility match the remainder of the input ? */
			$term_index = $in_term_index;
			$term = $current_term;
			$is_match = true;
			foreach ($trial_value as $trial_term)
			{
				if ($trial_term != $term)
				{
					$is_match = false;
					break;
				}
				$term = $this->get_term(++ $term_index);
			}
			
			if ($is_match)
			{
				if ($term == '.')
				/* are we at the end of the input and at a pattern termination ? */
				{
					if ($is_terminal) return $in_branch;
				}
				//else
				//{
				
				/* try and match the remainder of the input */
				$matched = $this->walk($in_branch, $term_index);
				if ($matched !== false)
				/* subbranch matched; this branch matched */
				{
					if ($in_save_match)
						$this->accumulate_wild_values($trial_value);
					
					return $matched;
				}
				//}
			}
		} /* foreach ($set_of_values as $trial_value) */
		
		return false; /* no match */
	}
	
	
	protected function try_match_wildcard($in_branch, &$in_wildcard, $in_term_index, $is_terminal)
	/* attempts to match the remainder of the input against a specific type of wildcard;
	saves the matching input if there's a match for later use (eg. AIML <star/> value) */
	{
		//print 'trying to match terms against wildcard...<br>';
		
		/* prepare to match wildcard */
		$current_term = $this->get_term($in_term_index);
		$wildcard_terms = array();
		$term_index = $in_term_index;
		$term = $current_term;
		$zero_or_more = JxBotEngine::is_zero_wildcard($in_wildcard);
		
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
			$matched = $this->walk($in_branch, $term_index);
			if ($matched !== false)
			/* subbranch matched; this branch matched */
			{
				$this->accumulate_wild_values($wildcard_terms);
				return $matched;
			}
			
			/* match the term to this wildcard */
			$wildcard_terms[] = $term;
			$term = $this->get_term(++ $term_index);
		}
	
		//print 'wildcard ran out of input<br>';
	
		/* reached end of input,
		inspect the wildcard match to see if matching
		should continue, or if we should fail here */
		$failed = (!$zero_or_more && count($wildcard_terms) == 0);
		if (!$failed)
		{				
			/* if submatching didn't run till the end,
			then this wildcard must be terminal */
			if ($is_terminal) 
			{
				$this->accumulate_wild_values($wildcard_terms);
				return $in_branch;
			}
		}
		
		/* run out of input on non-terminal; match failed */
		return false;
	}
	
	
	protected function try_match_word($in_branch, &$in_word, $in_term_index, $is_terminal)
	/* takes a word that has already been matched by the walk() function and attempts
	to match the remainder of the input; tracks progress of stack unwind on successful
	match so that saved wildcard values are added to the appropriate list */
	{
		//print 'trying to match term against word...<br>';
		
		//$current_term = $this->get_term($in_term_index);
		
		//if ($in_word == $current_term) // probably unnecessary, as SQL already matching...
		//{
		if ($this->get_term($in_term_index + 1) == '.')
		{
			if ($is_terminal) return $in_branch;
		}
		else
		{
			/* match remaining input to subbranch */
			$match = $this->walk($in_branch, $in_term_index + 1);
			if ($match !== false) 
			{
				if ($in_word == ':') $this->unwind_stage--;
				return $match;
			}
		}
		//}
			
		/* otherwise, match failed */
		//print 'failed<br>';
		return false;
	}
	
	
	protected function walk($in_parent_id, $in_term_index)
	/* Walks the subtree rooted at <parent_id>, beginning at term <term_index> of the
	user input; looks for a match with the remainder of the input.  If a match is found,
	returns the matching pattern ID, otherwise, returns FALSE. */
	{
		/* look in this branch for all possible matching sub-branches;
		ie. an exact match with the input term, or, a wildcard or complex pattern term
		such as a bot property or AIML 2 'set' */
		$stmt = JxBotDB::$db->prepare("SELECT id,expression,is_terminal FROM pattern_node 
			WHERE parent=? AND ( (expression = ? AND sort_key IN (0,5)) OR (sort_key NOT IN (0,5)) ) 
			ORDER BY sort_key");
		$current_term = $this->get_term($in_term_index);
		$stmt->execute(array($in_parent_id, $current_term));
		$possible_branches = $stmt->fetchAll(PDO::FETCH_NUM);
		
		//print "Walk  parent=$in_parent_id, term_index=$in_term_index, term=$current_term<br>";
		
		//print '<pre>';
		//var_dump($possible_branches);
		//print '</pre>';
		
		foreach ($possible_branches as $possibility)
		{
			/* decode the possibility and prepare to match */
			list($br_parent, $br_expr, $br_terminal) = $possibility;
			
			// in future, for speed, this information could be assessed at pattern registration
			// and stored & accessed, possibly using the sort key integer ?
			
			$is_wildcard = JxBotEngine::is_wildcard($br_expr);
			$set_ref = JxBotEngine::is_set_ref($br_expr);
			$bot_ref = JxBotEngine::is_bot_ref($br_expr);
			
			//print $br_expr;
			//print "Considering possible branch=$br_parent, expr=$br_expr, term=$br_terminal, wild=$is_wildcard  :<br>";
			
			// pattern side sets & bot tags will have to be handled similarly, since they may have multi-word values
			// basically, like wildcards, except all words must match
			
			/* branch to appropriate match handler depending on type of branch */
			if (($bot_ref !== false) || ($set_ref !== false))
			{
				/* match:  bot predicate or set reference: */
				$values = array( JxBotNL::normalise( JxBotConfig::bot($bot_ref) ) );
				$match = JxBotEngine::try_match_values($br_parent, $values, false, $in_term_index, $br_terminal);
			}
			else if (!$is_wildcard)
			{
				/* match:  normal word or pattern clause separator: */
				$match = JxBotEngine::try_match_word($br_parent, $br_expr, $in_term_index, $br_terminal);
			}
			else
			{
				/* match:  wildcard */
				$match = JxBotEngine::try_match_wildcard($br_parent, $br_expr, $in_term_index, $br_terminal);
			}
			
			/* if matching was successful, return the matching pattern,
			otherwise, continue looking at sub-branches at this level */
			if ($match !== false) return $match;
		}
		
		/* no possible subbranches; no match this branch */
		return false;
	}
	
	
	public static function match($in_input, $in_that, $in_topic)
	/* takes inputs in normal string form, returns some kind of array of information
	about the match (if any); assumes matching a single sentence (splitting already done) */
	{
		if (($in_that === null) || ($in_that === '')) $in_that = '*';
		if (($in_topic === null) || ($in_topic === '')) $in_topic = '*';
	
		$search_terms = JxBotNL::normalise($in_input);
		$search_terms[] = ':';
		if ($in_that == '*') $search_terms[] = $in_that;
		else $search_terms = array_merge($search_terms, JxBotNL::normalise($in_that));
		$search_terms[] = ':';
		if ($in_topic == '*') $search_terms[] = $in_topic;
		else $search_terms = array_merge($search_terms, JxBotNL::normalise($in_topic));
		
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

		$matched_pattern = $context->walk(0, 0);
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
		
		
		// for efficiency, we'd return ourselves as a match!
		
		$stmt = JxBotDB::$db->prepare('SELECT category FROM pattern WHERE id=?');
		$stmt->execute(array($matched_pattern));
		$category = $stmt->fetchAll(PDO::FETCH_NUM);
		$context->matched_category_id = $category[0][0];
		
		
		/*return array(
			'category'=>$category,
			'star'=>$context->wild_values,
			'thatstar'=>$context->wild_that_values,
			'topicstar'=>$context->wild_topic_values
		);*/
		
		return $context;
	}
	
	
	public function matched_category()
	{
		return $this->matched_category_id;
	}
	
	
	public function input_capture($in_index)
	{
		return $this->wild_values[$in_index];
	}
	
	
	public function that_capture($in_index)
	{
		return $this->wild_that_values[$in_index];
	}
	
	
	public function topic_capture($in_index)
	{
		return $this->wild_topic_values[$in_index];
	}
	
}





