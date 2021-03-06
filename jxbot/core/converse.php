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

/* conversation management; initialisation, logging, client predicates ('variables'),
response generation using the engine */

if (!defined('JXBOT')) die('Direct script access not permitted.');


class JxBotConverse
{

/********************************************************************************
Conversaton Specific Properties
*/
	private static $convo_id = '';  /* the public ID string associated with the session */
	private static $session_id = 0; /* the internal session identifier */
	
	private static $predicates = array(); /* cache; array of predicate key => values */
	
	private static $match_time = 0.0; /* total accumulated match time
	                                     during this interaction / HTTP request */
	private static $service_time = 0.0; /* total accumulated external service req.
	                                     time during this interaction / HTTP request */
	                                     
	private static $srai_level = 0;   /* track how deep in srai() we are
	                                     to prevent infinite recursion */
	                                     
	private static $iq_score = 0.0;   /* total IQ score for top-level activated patterns */
	
	private static $category_stack;   /* stack of activated categories;
	                                     helps to track & prevent excessive recursion */
	                                     
	
	const MAX_SRAI_RECURSION = 15;
	const MAX_CATEGORY_NESTING = 2; /* a category cannot be recursively invoked 
                                       beyond this limit; will return no output */
	

/********************************************************************************
Utilities
*/

	public static function bot_available()
	/* returns TRUE if the bot has been made available through the administration panel;
	if the bot is not available, the public interface should not allow client interaction
	with the bot. */
	{
		return (intval(JxBotConfig::option('bot_active')) !== 0);
	}
	
	
/********************************************************************************
Logging
*/

	private static function log(&$input, &$output, $time_total, $time_match, $time_service, $intel)
	/* logs the latest interaction with the bot for later analysis by the administrator */
	{
		if (JxBotConverse::$session_id === 0) return;
		
		$stmt = JxBotDB::$db->prepare('
			INSERT INTO log (session, input, output, 
				time_respond, time_match, time_service, intel_score) 
			VALUES (?, ?, ?, ?, ?, ?, ?)');
		$stmt->execute(array(JxBotConverse::$session_id, $input, $output, 
			$time_total, $time_match, $time_service, $intel));
	}
	
	
	public static function latest_sessions()
	/* retrieves a list of the most recent conversations for use in the administration
	log explorer */
	{
		$stmt = JxBotDB::$db->prepare('
			SELECT session.id,session.name
			FROM session 
			ORDER BY accessed DESC
			LIMIT 50
			');
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_NUM);
		
		$convos = array();
		foreach ($rows as $row)
		{
			if (trim($row[1]) === '')
				$convos[] = array($row[0], 'Anonymous '.$row[0]);
			else
				$convos[] = array($row[0], $row[1]);
		}
		
		return $convos;
	}
	

/********************************************************************************
Client Predicates
*/

	public static function set_client_name($in_name)
	/* set's the client name associated with the current conversation;
	initially this is automatically determined, however, a template may change the value
	based on interaction with the user */
	{
		$stmt = JxBotDB::$db->prepare('UPDATE session SET name=? WHERE id=?');
		$stmt->execute(array(trim($in_name), JxBotConverse::$session_id));
		
		JxBotConverse::$predicates['name'] = $in_name;
	}
	
	
	public static function set_predicate($in_name, $in_value)
	/* save a predicate value for the client/conversation */
	{
		if ($in_name == 'name')
		{
			JxBotConverse::set_client_name($in_value);
			return;
		}
		
		$did_add = false;
		try
		{
			$stmt = JxBotDB::$db->prepare('INSERT INTO predicate (session, name, value) VALUES (?, ?, ?)');
			$stmt->execute(array(JxBotConverse::$session_id, $in_name, $in_value));
			$did_add = true;
		}
		catch (Exception $err) {}
		
		if (!$did_add)
		{
			$stmt = JxBotDB::$db->prepare('UPDATE predicate SET value=? WHERE session=? AND name=?');
			$stmt->execute(array($in_value, JxBotConverse::$session_id, $in_name));
		}
		
		JxBotConverse::$predicates[$in_name] = $in_value;
	}
	
	
	public static function predicate($in_name)
	/* retrieve a predicate value for the client/conversation;
	if a value is used multiple times within a template, the value will be cached
	to minimise database queries */
	{
	//print 'Get predicate: '.$in_name.'<br>';
		if ($in_name == 'id')
			return JxBotConverse::$session_id;
	
		if (!isset(JxBotConverse::$predicates[$in_name]))
		{
			$stmt = JxBotDB::$db->prepare('SELECT value FROM predicate WHERE session=? AND name=?');
			$stmt->execute(array(JxBotConverse::$session_id, $in_name));
			$row = $stmt->fetchAll(PDO::FETCH_NUM);
			if (count($row) == 0) 
			{
				JxBotConverse::$predicates[$in_name] = JxBotConfig::default_predicate($in_name);
			}
			else
				JxBotConverse::$predicates[$in_name] = $row[0][0];
		}
		return JxBotConverse::$predicates[$in_name];
	}
	

/********************************************************************************
Conversation
*/

	public static function resume_conversation($in_convo_id)
	/* begins/resumes a conversation; requires a unique conversation ID which ordinarily
	should be the session ID used by any cookie in use with the client HTTP browser */
	{
		/* generate a conversation ID if required */
		if (($in_convo_id === null) || ($in_convo_id === ''))
			$in_convo_id = hash('sha256', time());
	
		/* check if the conversation is registered */
		$stmt = JxBotDB::$db->prepare('SELECT id, name, accessed >= DATE_SUB(NOW(), INTERVAL 15 MINUTE) 
			FROM session WHERE convo_id=?');
		$stmt->execute(array($in_convo_id));
		$session_id = $stmt->fetchAll(PDO::FETCH_NUM);
		
		/* change the conversation ID if required */
		if (($in_convo_id != 'admin') && (count($session_id) == 1) && ($session_id[0][2] == 0))
		{
			$in_convo_id = hash('sha256', time());
			$session_id = array();
		}
		
		/* register the conversation */
		if (count($session_id) == 0)
		{
			if ($in_convo_id == 'admin') $name = 'Administrator';
			else $name = '';
		
			$stmt = JxBotDB::$db->prepare('INSERT INTO session (convo_id, name) VALUES (?, ?)');
			$stmt->execute(array($in_convo_id, $name));
			$session_id = JxBotDB::$db->lastInsertId();
		}
		else 
		{
			$name = $session_id[0][1];
			$session_id = $session_id[0][0];
			
			$stmt = JxBotDB::$db->prepare('UPDATE session SET accessed=CURRENT_TIMESTAMP WHERE id=?');
			$stmt->execute(array($session_id));
		}
		
		/* store conversation IDs for this request */
		JxBotConverse::$convo_id = $in_convo_id;
		JxBotConverse::$session_id = $session_id;
		JxBotConverse::$predicates['name'] = $name;
		
		return $in_convo_id;
	}
	
	
	// ! ** may want to improve the efficiency of these
	// they're operating on a non-indexed column
	// consider i) indexing the session ID in log,
	// or ii) providing a dedicated history table
	// or iii) storing as special session predicate values (as most wont be
	// accessed regularly anyway)
	
	public static function history_request($in_index)
	{
		$in_index = intval($in_index);
		if ($in_index < 0) return '';
		
		$stmt = JxBotDB::$db->prepare('SELECT input FROM log 
			WHERE session=? ORDER BY id DESC LIMIT '.$in_index.',1');
		$stmt->execute(array(JxBotConverse::$session_id));
		$row = $stmt->fetchAll(PDO::FETCH_NUM);
		if (count($row) == 0) return '';
		else return $row[0][0];
	}
	
	
	public static function history_response($in_index)
	{
		$in_index = intval($in_index);
		if ($in_index < 0) return '';
		
		$stmt = JxBotDB::$db->prepare('SELECT output FROM log 
			WHERE session=? ORDER BY id DESC LIMIT '.$in_index.',1');
		$stmt->execute(array(JxBotConverse::$session_id));
		$row = $stmt->fetchAll(PDO::FETCH_NUM);
		if (count($row) == 0) return '';
		else return $row[0][0];
	}
	
	
	private static function sentence_respond(&$in_input)
	{
		/* find a category for the input */
		$start_time = microtime(true);
		
		$match = JxBotEngine::match($in_input, 
			JxBotConverse::history_response(0), // review if specific sentence required here?
			JxBotConverse::predicate('topic') );
			
		$end_time = microtime(true);
		JxBotConverse::$match_time += ($end_time - $start_time);
		
		/* check recursion */
		$category = ($match === false ? -1 : $match->matched_category());
		$count = 0;
		foreach (JxBotConverse::$category_stack as $nested_category)
			if ($nested_category == $category) $count++;
		if ($count >= JxBotConverse::MAX_CATEGORY_NESTING) 
		{
			//print 'CATEGORY LIMIT<br>';
			return '';
		}
		JxBotConverse::$category_stack[] = $category;
		
		if ($match === false)
		/* no match was found; the input was not understood
		and no default category is available */
			$output = '???';
		else
		{
			/* select a template at random */
			//print 'MATCHED CATEGORY '.$category.'<br>';
			$template = JxBotNLData::fetch_templates( $category );
			$count = count($template);
			if ($count == 0) $output = '???';
			else 
			{
				if ($count == 1) $index = 0;
				else $index = mt_rand(1, $count) - 1;
				$output = $template[$index][1];
			}
			if (JxBotConverse::$srai_level == 1)
				JxBotConverse::$iq_score += $match->iq_score();
		}
		
		/* generate the template */
		$template = JxBotAiml::parse_template($output);
		$output = $template->generate( $match );
		
		/* track recursion */
		array_pop(JxBotConverse::$category_stack);
		
		return $output;
	}
	
	
	public static function srai($in_input)
	/* evaluate the input within the current context and generate output,
	without logging the output or updating the history */
	{
		//print 'SRAI '.$in_input. '<br>';
		/* check recursion level */
		JxBotConverse::$srai_level ++;
		if (JxBotConverse::$srai_level > JxBotConverse::MAX_SRAI_RECURSION)
			throw new Exception('Too much recursion (in SRAI)');
	
		/* process each sentence separately */
		$sentences = JxBotNL::split_sentences($in_input);
		$output = array();
		foreach ($sentences as $sentence)
			$output[] = JxBotConverse::sentence_respond($sentence);
		$output = implode(' ', $output);
		
		JxBotConverse::$srai_level --;
		return $output;
	}


	private static function user_input_looks_strange(&$in_input)
	{
		if (mb_strlen($in_input) > 255) return true;
		
		// could also check for stuff that looks like computer code,
		// or lots of low-level control characters ** TODO
		
		return false;
	}


	public static function get_response($in_input)
	/* causes the bot to generate a response to the supplied client input */
	{
		if (JxBotConverse::user_input_looks_strange($in_input))
			return 'Your last comment looks a bit strange.'; // ** configurable?
	
		/* cap general server requests (safety); should be configurable
		as people have different host specs; 300 recommended for small shared host */
		$cap_bot_ipm = JxBotConfig::option('sys_cap_bot_ipm', 300);
		if ($cap_bot_ipm > 0)
		{
			$stmt = JxBotDB::$db->prepare('SELECT COUNT(*) FROM log 
				WHERE stamp >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)');
			$stmt->execute();
			$last_min_total = intval( $stmt->fetchAll(PDO::FETCH_NUM)[0][0] );
			if ($last_min_total >= $cap_bot_ipm)
				return 'Sorry, I\'m too busy to chat right now.  Please come back later.';
		}
		
		/* count interaction */
		JxBotDB::$db->exec('UPDATE stats SET interactions = interactions + 1');
	
		/* start timer */
		$start_time = microtime(true);
		
		/* initalize tracking variables */
		JxBotConverse::$match_time = 0.0;
		JxBotConverse::$service_time = 0.0;
		JxBotConverse::$iq_score = 0.0;
		JxBotConverse::$category_stack = array();
		$fault = false;
		
		/* run the bot */
		try
		{
			$output = JxBotConverse::srai($in_input);
		}
		catch (Exception $err)
		{
			$output = $err->getMessage();
			$fault = true;
		}
		
		/* end timer */
		$end_time = microtime(true);
		
		//print 'IQ '.JxBotConverse::$iq_score. '<br>';
		
		/* log this interaction */
		if (trim($output) == '') $fault = true;
		if ($fault) JxBotConverse::$iq_score = -1;
		JxBotConverse::log($in_input, $output, 
			($end_time - $start_time), JxBotConverse::$match_time, 
			JxBotConverse::$service_time, JxBotConverse::$iq_score);
		
		/* return the bot response */
		if ($fault)
			return 'I do apologise.  I seem to be experiencing a positronic malfunction.';
		return $output;
	}
	
	
	public static function get_greeting()
	/* returns an appropriate greeting to the client when they first connect */
	{
		$output = 'Hello.';
		
		// ! TODO: this should probably be configurable **
		
		$blank = '';
		//JxBotConverse::log($blank, $output, 0, 0);
		
		return $output;
	}
	
	// suggest a salutation matching phase
	// where only salutations (indexed flag) are matched
	// followed by a separate stage
}


