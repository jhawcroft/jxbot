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

	private static function log(&$input, &$output)
	/* logs the latest interaction with the bot for later analysis by the administrator */
	{
		if (JxBotConverse::$session_id === 0) return;
		
		$stmt = JxBotDB::$db->prepare('INSERT INTO log (session, input, output) VALUES (?, ?, ?)');
		$stmt->execute(array(JxBotConverse::$session_id, $input, $output));
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
				$convos[] = array($row[0], 'Client '.$row[0]);
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
		if ($in_name == 'id')
			return JxBotConverse::$session_id;
	
		if (!isset(JxBotConverse::$predicates[$in_name]))
		{
			$stmt = JxBotDB::$db->prepare('SELECT value FROM predicate WHERE session=? AND name=?');
			$stmt->execute(array(JxBotConverse::$session_id, $in_name));
			$row = $stmt->fetchAll(PDO::FETCH_NUM);
			if (count($row) == 0) 
				JxBotConverse::$predicates[$in_name] = JxBotConfig::default_predicate($in_name);
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
		/* check if the conversation is registered */
		$stmt = JxBotDB::$db->prepare('SELECT id,name FROM session WHERE convo_id=?');
		$stmt->execute(array($in_convo_id));
		$session_id = $stmt->fetchAll(PDO::FETCH_NUM);
		
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
	}
	
	
	public static function srai($in_input)
	/* evaluate the input within the current context and generate output,
	without logging the output or updating the history */
	{
		$match = JxBotEngine::match($in_input, 
			JxBotConverse::predicate('that'), JxBotConverse::predicate('topic') );
		
		if ($match === false)
		/* no match was found; the input was not understood
		and no default category is available */
			$output = '???'; // ! TODO: this should probably be configurable **
		else
		{
			$template = JxBotNLData::fetch_templates( $match->matched_category() );
			// implement random
			$output = $template[0][1];
		}
		
		$template = JxBotAiml::parse_template($output);
		$output = $template->generate( $match );
		
		return $output;
	}


	public static function get_response($in_input)
	/* causes the bot to generate a response to the supplied client input */
	{
		$output = JxBotConverse::srai($in_input);
		
		JxBotConverse::log($in_input, $output);
		JxBotConverse::set_predicate('that', $output); // probably don't need this if we use log
		
		return $output;
	}
	
	
	public static function get_greeting()
	/* returns an appropriate greeting to the client when they first connect */
	{
		$output = 'Hello.';
		
		// ! TODO: this should probably be configurable **
		
		$blank = '';
		JxBotConverse::log($blank, $output);
		
		return $output;
	}
	
	// suggest a salutation matching phase
	// where only salutations (indexed flag) are matched
	// followed by a separate stage
}


