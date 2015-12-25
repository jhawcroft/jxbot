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
 

class JxBotConfig
{
	const SESSION_NAME = 'JoshixBot';
	

	private static $config = array();
	private static $is_installed = false;
	
	
	public static function is_installed()
	{
		return JxBotConfig::$is_installed;
	}
	
	
	public static function bot_url()
	{
		return JxBotConfig::$config['bot_url'];
	}
	

	public static function setup_environment()
	{
		$config_file = dirname(dirname(__FILE__)).'/config.php';
		if (!is_readable($config_file)) return; /* not installed */
		
		$jxbot = array();
		require_once($config_file);
		JxBotConfig::$config = $jxbot;
		
		if (!isset($jxbot['bot_url']))
			JxBot::fatal_error("Bot configuraton is missing bot_url.");
		
		if (isset($jxbot['debug']) && $jxbot['debug'])
		{
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
		}
		
		if (!isset($jxbot['db_host']))
			JxBot::fatal_error("JxBot database not configured.");
		if (!isset($jxbot['db_name']))
			JxBot::fatal_error("JxBot database not configured.");
		if (!isset($jxbot['db_prefix']))
			$jxbot['db_prefix'] = '';
		if (!isset($jxbot['db_username']))
			JxBot::fatal_error("JxBot database not configured.");
		if (!isset($jxbot['db_password']))
			JxBot::fatal_error("JxBot database not configured.");
		
		JxBotDB::connect($jxbot['db_host'], $jxbot['db_name'], $jxbot['db_prefix'],
			$jxbot['db_username'], $jxbot['db_password']);
			
		JxBotConfig::$is_installed = true;
		
		if (isset($jxbot['timezone']))
			date_default_timezone_set($jxbot['timezone']);
			
		JxBotConfig::load_configuration();
	}
	
	
	public static function option($in_key)
	{
		if (isset(JxBotConfig::$config[$in_key])) return JxBotConfig::$config[$in_key];
		return null;
	}	
	
	
	public static function set_option($in_key, $in_value)
	{
		JxBotConfig::$config[$in_key] = $in_value;
	}
	
	
	public static function delete_option($in_key)
	{
		if (isset(JxBotConfig::$config[$in_key]))
			unset(JxBotConfig::$config[$in_key]);
		$stmt = JxBotDB::$db->prepare('DELETE FROM opt WHERE opt_key=?');
		$stmt->execute(array($in_key));
	}
	
	
	public static function load_configuration()
	{
		$stmt = JxBotDB::$db->prepare('SELECT opt_key, opt_value FROM opt');
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_NUM);
		foreach ($rows as $row)
		{
			JxBotConfig::$config[$row[0]] = $row[1];
		}
	}
	
	
	public static function save_configuration()
	{
		foreach (JxBotConfig::$config as $key => $value)
		{	
			if (substr($key, 0, 3) == 'db_') continue;
			if (substr($key, 0, 7) == 'config_') continue;
			if ($key == 'debug') continue;
			if ($key == 'bot_url') continue;
			try
			{
				$stmt = JxBotDB::$db->prepare('INSERT INTO opt (opt_value, opt_key) VALUES (?, ?)');
				$stmt->execute(array($value, $key));
			}
			catch (Exception $err) {}
			try
			{
				$stmt = JxBotDB::$db->prepare('UPDATE opt SET opt_value=? WHERE opt_key=?');
				$stmt->execute(array($value, $key));
			}
			catch (Exception $err) {}
		}    
	}
	
	
	public static function bot($in_key)
	{
		
	}
	
	
	public static function bot_properties()
	{
		$props = array();
		
		$stmt = JxBotDB::$db->prepare('SELECT opt_key, opt_value FROM opt ORDER BY opt_key');
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_NUM);
		foreach ($rows as $row)
		{
			if (substr($row[0], 0, 4) !== 'bot_') continue;
			if ($row[0] === 'bot_name') continue;
			if ($row[0] === 'bot_tz') continue;
			if ($row[0] === 'bot_active') continue;
			
			$nice_name = ucwords(str_replace('_', ' ', substr($row[0], 4)));
			$props[] = array($row[0], $nice_name, $row[1]);
		}
		
		return $props;
	}
	
	
	public static function bot_add_prop($in_name)
	{
		$prop_id = str_replace(' ', '_', strtolower(trim($in_name)));
		JxBotConfig::set_option('bot_'.$prop_id, '');
	}
	
	
	public static function bot_delete_prop($in_id)
	{
		JxBotConfig::delete_option($in_id);
	}
	
	
	
	public static function client_defaults()
	{
		$props = array();
		
		$stmt = JxBotDB::$db->prepare('SELECT opt_key, opt_value FROM opt ORDER BY opt_key');
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_NUM);
		foreach ($rows as $row)
		{
			if (substr($row[0], 0, 4) !== 'def_') continue;
			
			$nice_name = ucwords(str_replace('_', ' ', substr($row[0], 4)));
			$props[] = array($row[0], $nice_name, $row[1]);
		}
		
		return $props;
	}
	
	
	public static function def_add_pred($in_name)
	{
		$prop_id = str_replace(' ', '_', strtolower(trim($in_name)));
		JxBotConfig::set_option('def_'.$prop_id, '');
	}
	
	
	public static function def_delete_pred($in_id)
	{
		JxBotConfig::delete_option($in_id);
	}
	
	
	public static function default_predicate($in_name)
	{
		return JxBotConfig::option('def_'.$in_name);
	}
}









