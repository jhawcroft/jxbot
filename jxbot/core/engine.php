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
			return  ':'; /* ** not sure about the character selected here; needs review;
			                could just as well be a period? */
	}
	
	
	protected function walk($in_parent_id, $in_term_index)
	/* Walks the subtree rooted at <parent_id>, beginning at term <term_index> of the
	user input; looks for a match with the remainder of the input.  If a match is found,
	returns the matching pattern ID, otherwise, returns FALSE. */
	{
		$current_term = $this->get_term($in_term_index);
		
		
		// can this be refactored into a number of smaller functions??! - 3 subparts
		
		
		
		//print "Walk  parent=$in_parent_id, term_index=$in_term_index, term=$current_term<br>";
		
		/* look in this branch for all possible matching subbranches */
		$stmt = JxBotDB::$db->prepare("SELECT id,expression,is_terminal FROM pattern_node 
			WHERE parent=? AND ( (expression = ? AND sort_key IN (0,5)) OR (sort_key NOT IN (0,5)) ) 
			ORDER BY sort_key");
		$stmt->execute(array($in_parent_id, $current_term));
		$possible_branches = $stmt->fetchAll(PDO::FETCH_NUM);
		
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
			
			if (($bot_ref !== false) || ($set_ref !== false))
			{
				// grab the set of possible match values
				if ($bot_ref !== false)
				{
					$set_of_values = array( JxBotNL::normalise( JxBotConfig::bot($bot_ref) ) );
				}
				else if ($set_ref !== false)
				{
					// need to get all set values here *** todo
					$set_of_values = array();
				}
				
				// attempt to match all words at current position to one of the set values
				foreach ($set_of_values as $trial_value)
				{
					//print 'Trying '.implode(' ', $trial_value).'<br>';
					//var_dump($current_term);
					
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
						$matched = $this->walk($br_parent, $term_index);
						if ($matched !== false)
						/* subbranch matched; this branch matched */
						{
							if ($set_ref !== false)
								$this->accumulate_wild_values($trial_value);
								
							if ($br_expr === ':') $this->unwind_stage--;
							return $matched;
						}
					}
				}
				// failed.. continue with other branch possibilities
			}
			else if (!$is_wildcard)
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
	
		$search_terms = JxBotNL::normalise($in_input);
		$search_terms[] = ':';
		$search_terms = array_merge($search_terms, JxBotNL::normalise($in_that));
		$search_terms[] = ':';
		$search_terms = array_merge($search_terms, JxBotNL::normalise($in_topic));
		
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
		
		$stmt = JxBotDB::$db->prepare('SELECT category FROM pattern WHERE id=?');
		$stmt->execute(array($matched_pattern));
		$category = $stmt->fetchAll(PDO::FETCH_NUM);
		$category = $category[0][0];
		
		return $category;
	}
	
}





