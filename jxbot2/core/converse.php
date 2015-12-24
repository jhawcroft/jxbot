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
 

class Converse
{

	private static $convo_id = '';
	
	
	public static function bot_available()
	{
		global $jxbot_config;
		return (intval($jxbot_config['bot_active']) !== 0);
	}
	
	
	private static function log(&$input, &$output)
	{
		if (Converse::$convo_id === '') return;
		
		return;
		
		$stmt = JxBotDB::$db->prepare('INSERT INTO log (input, output, convo_id) VALUES (?, ?, ?)');
		$stmt->execute(array($input, $output, Converse::$convo_id));
	}
	
	
	public static function resume_conversation($in_convo_id)
	{
		Converse::$convo_id = $in_convo_id;
	}


	public static function get_response($in_input)
	{
		//$words = NLAux::normalise($in_input);
		//$category_id = NL::match_input($in_input);
		$category_id = JxBotEngine::match($in_input, '', '');
		print 'Matched category: '.$category_id.'<br>';
		$template = JxBotEngine::fetch_templates($category_id);
		$output = $template[0][1];
		//$output = NL::make_output($category_id);
		
		Converse::log($in_input, $output);
		
		return $output;
	}
	
	
	public static function get_greeting()
	/* conversation has just begun;
	return an appropriate salutation */
	{
		$output = 'Hello.';
		
		$blank = '';
		Converse::log($blank, $output);
		
		return $output;
	}
	
	// suggest a salutation matching phase
	// where only salutations (indexed flag) are matched
	// followed by a separate stage
}


