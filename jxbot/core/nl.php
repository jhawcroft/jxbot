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

class NL
{
	public static $last_category_id = 0;
	
//	private static $_flag_make_cat = true;

	// whatever the last used category is should be the default
	/*public static function new_category()
	{
		NL::$_flag_make_cat = true;
	}
	
	
	private static function _new_category()
	{
		global $jxbot_db;
		$jxbot_db->exec('INSERT INTO category VALUES (NULL)');
		NL::$last_category_id = $jxbot_db->lastInsertId();
		NL::$_flag_make_cat = false;
	}*/
	
	
	
	

	public static function register_sequence($in_category_id, $in_sequence)
	{
		$words = NLAux::normalise($in_sequence);
		
		$stmt = JxBotDB::$db->prepare('INSERT INTO sequence (category_id, words) VALUES (?, ?)');
		$stmt->execute(array( $in_category_id, implode(',', $words) ) );
		$sequence_id = JxBotDB::$db->lastInsertId();
		
		foreach ($words as $word)
		{
			$word_id = NLAux::get_word_id($word);
			$stmt = JxBotDB::$db->prepare('INSERT INTO sequence_word VALUES (?, ?)');
			$stmt->execute(array($sequence_id, $word_id));
		}
	}

	
	public static function register_template($in_template)
	{
		global $jxbot_db;
		/*if (NL::$_flag_make_cat) NL::_new_category();*/
		assert(NL::$last_category_id != 0);
		
		$stmt = $jxbot_db->prepare('INSERT INTO template (category_id, template) VALUES (?, ?)');
		$stmt->execute(array(NL::$last_category_id, $in_template));
	}
	
	
	public static function quote_word(&$io_word)
	{
		global $jxbot_db;
		$io_word = $jxbot_db->quote($io_word);
	}
	
	
	public static function prefind_sequences($in_words)
	{
		global $jxbot_db;
		
		array_walk($in_words, array('NL', 'quote_word'));
		$sql = 'SELECT category.id, sequence.sequence_id, sequence.words
		FROM sequence JOIN sequence_word ON sequence.sequence_id=sequence_word.sequence_id
		AND sequence_word.word_id IN (SELECT DISTINCT word_id FROM word WHERE word.word IN ('.
		implode(',', $in_words).')) JOIN category ON sequence.category_id=category.id
		GROUP BY sequence.sequence_id';
		//print $sql.'<br>';
		
		$stmt = $jxbot_db->prepare($sql);
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_NUM);
		
		return $rows;
	}
	
	
	public static function exact_sequence_exists($in_sequence)
	{
		$words = NLAux::normalise($in_sequence);
		$normalised = implode(',', $words);
		array_walk($words, array('NL', 'quote_word'));
		
		$sql = 'SELECT category.id, sequence.sequence_id, sequence.words
		FROM sequence JOIN sequence_word ON sequence.sequence_id=sequence_word.sequence_id
		AND sequence_word.word_id IN (SELECT DISTINCT word_id FROM word WHERE word.word IN ('.
		implode(',', $words).')) JOIN category ON sequence.category_id=category.id
		WHERE sequence.words=?
		GROUP BY sequence.sequence_id';
		
		$stmt = JxBotDB::$db->prepare($sql);
		$stmt->execute(array($normalised));
		$rows = $stmt->fetchAll(PDO::FETCH_NUM);
		
		if (count($rows) == 0) return false;
		return $rows;
	}
	
	
	public static function fetch_templates($in_category_id)
	{
		global $jxbot_db;
		
		$stmt = $jxbot_db->prepare('SELECT template FROM template WHERE category_id=?');
		$stmt->execute(array($in_category_id));
		$rows = $stmt->fetchAll(PDO::FETCH_NUM);
		
		return array_column($rows, 0);
	}
	
	
	// we should choose the longest matching sequence
	// it would be handy to have parameterised sequences
	
	// lets get it working & built without parameterised sequences to begin with
	// then i'll have something to build upon
	
	// templates should be selected to avoid saying the same thing 
	// as was previously said
	// but we can just choose randomly for now
	
	// eventually could store a word length with each sequence
	// so database can sort and we can peruse from longest to shortest
	
	public static function best_match(&$in_words, &$in_sequences)
	{
		return $in_sequences[0][0];
		
		// check length at least; first optimisation scheduled for later
	}
	
	
	public static function match_input($in_words)
	{
		$sequences = NL::prefind_sequences($in_words);
		return NL::best_match($in_words, $sequences);
	}
	
	
	public static function make_output($in_category_id)
	{
		$templates = NL::fetch_templates($in_category_id);
		return $templates[0];
	}
	
}

// find categories by providing utterances that match a pattern
// or that exactly match a sequence ( a category is a way of logically grouping
// patterns that match essentially the same meaning / semantics )

// merge sequences eventually
// and templates

// split sequences eventually
// and templates

// use a simple loader mechanism to avoid having to write a massive interface
// initially?

