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
 
// auxiliary natural-language routines
// such as dictionaries, etc.

class NLAux
{
	public static function get_word_id($in_word)
	// looks up the internal word ID or creates a new entry in the table
	// if the word doesn't already exist
	{
		if (strlen($in_word) > BotLimits::MAX_WORD_LENGTH)
			bot_die('NLAux::get_word_id() word length exceeds limit ('. BotLimits::MAX_WORD_LENGTH . ')');
		
		global $jxbot_db;
		$stmt = $jxbot_db->prepare('SELECT word_id FROM word where word=?');
		$stmt->execute(array($in_word));
		$row = $stmt->fetchAll(PDO::FETCH_NUM);
		if (count($row) == 0)
		{
			$stmt = $jxbot_db->prepare('INSERT INTO word (word) VALUES (?)');
			$stmt->execute(array($in_word));
			return $jxbot_db->lastInsertId();
		}
		else return intval($row[0][0]);
	}
	
	
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

