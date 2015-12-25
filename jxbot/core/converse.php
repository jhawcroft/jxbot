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
 

class JxBotConverse
{

	private static $convo_id = '';  /* the public ID string associated with the session */
	private static $session_id = 0; /* the internal session identifier */
	
	private static $predicates = array();
	
	
	public static function bot_available()
	{
		return (intval(JxBotConfig::option('bot_active')) !== 0);
	}
	
	
	private static function log(&$input, &$output)
	{
		if (JxBotConverse::$session_id === 0) return;
		
		$stmt = JxBotDB::$db->prepare('INSERT INTO log (session, input, output) VALUES (?, ?, ?)');
		$stmt->execute(array(JxBotConverse::$session_id, $input, $output));
	}
	
	
	public static function latest_sessions()
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
	
	
	public static function set_client_name($in_name)
	{
		$stmt = JxBotDB::$db->prepare('UPDATE session SET name=? WHERE id=?');
		$stmt->execute(array(trim($in_name), JxBotConverse::$session_id));
		
		JxBotConverse::$predicates['name'] = $in_name;
	}
	
	
	public static function set_predicate($in_name, $in_value)
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
	{
		if (!isset(JxBotConverse::$predicates[$in_name]))
		{
			$stmt = JxBotDB::$db->prepare('SELECT value FROM predicate WHERE session=? AND name=?');
			$stmt->execute(array(JxBotConverse::$session_id, $in_name));
			$row = $stmt->fetchAll(PDO::FETCH_NUM);
			if (count($row) == 0) return null;
			
			JxBotConverse::$predicates[$in_name] = $row[0][0];
		}
		return JxBotConverse::$predicates[$in_name];
	}
	
	
	public static function resume_conversation($in_convo_id)
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


	public static function get_response($in_input)
	{
		//$words = NLAux::normalise($in_input);
		//$category_id = NL::match_input($in_input);
		$category_id = JxBotEngine::match($in_input, 'unknown', 'unknown');
		//print 'Matched category: '.$category_id.'<br>';
		$template = JxBotEngine::fetch_templates($category_id);
		$output = $template[0][1];
		//$output = NL::make_output($category_id);
		
		JxBotConverse::log($in_input, $output);
		
		return $output;
	}
	
	
	public static function get_greeting()
	/* conversation has just begun;
	return an appropriate salutation */
	{
		$output = 'Hello.';
		
		$blank = '';
		JxBotConverse::log($blank, $output);
		
		return $output;
	}
	
	// suggest a salutation matching phase
	// where only salutations (indexed flag) are matched
	// followed by a separate stage
}


