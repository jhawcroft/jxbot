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

mb_internal_encoding('UTF-8');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// limits

class BotLimits
{
	const MAX_WORD_LENGTH = 30;
}


class BotDefaults
{
	const SESSION_NAME = 'JoshixBot';
	
	const VERSION = '1.0.0';
	
	
	public static function load_configuration()
	{
		global $jxbot_db, $jxbot_config;
		$stmt = $jxbot_db->prepare('SELECT opt_key, opt_value FROM opt');
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_NUM);
		foreach ($rows as $row)
		{
			$jxbot_config[$row[0]] = $row[1];
		}
	}
	
	
	public static function save_configuration()
	{
		global $jxbot_db, $jxbot_config;
		foreach ($jxbot_config as $key => $value)
		{
			if (substr($key, 0, 3) == 'db_') continue;
			if (substr($key, 0, 7) == 'config_') continue;
			if ($key == 'debug') continue;
			try
			{
				$stmt = $jxbot_db->prepare('INSERT INTO opt (opt_value, opt_key) VALUES (?, ?)');
				$stmt->execute(array($value, $key));
			}
			catch (Exception $err) {}
			try
			{
				$stmt = $jxbot_db->prepare('UPDATE opt SET opt_value=? WHERE opt_key=?');
				$stmt->execute(array($value, $key));
			}
			catch (Exception $err) {}
		}    
	}
	
	
	public static function bot_url()
	{
		global $jxbot_config;
		return $jxbot_config['bot_url'];
	}


	public static function option($in_key)
	{
		global $jxbot_config;
		if (isset($jxbot_config[$in_key])) return $jxbot_config[$in_key];
		return null;
	}	
	
	
	public static function set_option($in_key, $in_value)
	{
		global $jxbot_config;
		$jxbot_config[$in_key] = $in_value;
	}
}

