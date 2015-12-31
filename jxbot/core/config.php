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

/* bot configuration and initalisation */

if (!defined('JXBOT')) die('Direct script access not permitted.');


class JxBotConfig
{
	const SESSION_NAME = 'JoshixBotAdmin';
	

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
	
	
	private static function run_installer()
	{
		require_once(dirname(__FILE__).'/install.php');
		exit;
	}
	

	public static function setup_environment()
	{
		mb_internal_encoding('UTF-8');
	
		JxBotConfig::$config['bot_url'] = JxBotUtil::request_url();

		$config_file = dirname(dirname(__FILE__)).'/config.php';
		if (!is_readable($config_file)) return JxBotConfig::run_installer();
		
		if (!JxBotConfig::load_config())
			JxBot::fatal_error("Couldn't load database configuration.");
			
		/*$jxbot = array();
		require_once($config_file);
		JxBotConfig::$config = $jxbot;
		
		if (!isset($jxbot['bot_url']))
			JxBot::fatal_error("Bot configuraton is missing bot_url.");*/
		
		/*if (isset($jxbot['debug']) && $jxbot['debug']) 
		{
			// PHP debugging for the program; distinct from AIML debugging
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
		}*/
		
		/*if (!isset($jxbot['db_host']))
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
			$jxbot['db_username'], $jxbot['db_password']);*/
			
		if (!JxBotConfig::try_connect_db())
			JxBot::fatal_error("Couldn't connect to database.");
			
		JxBotConfig::load_configuration();
		
		JxBotConfig::$is_installed = true;
		
		date_default_timezone_set( JxBotConfig::option('bot_tz') );
	}
	
	
	public static function load_config()
	{
		if (isset(JxBotConfig::$config['db_name'])) return true;
		
		$config_file = dirname(dirname(__FILE__)).'/config.php';
		if (!is_readable($config_file)) return false;
		
		$jxbot = array();
		require_once($config_file);
		JxBotConfig::$config = $jxbot;
		
		return true;
	}
	
	
	public static function try_connect_db()
	{
		return JxBotDB::connect(
			JxBotConfig::option('db_host'), 
			JxBotConfig::option('db_name'), 
			JxBotConfig::option('db_prefix'),
			JxBotConfig::option('db_username'), 
			JxBotConfig::option('db_password')
			);
	}
	
	
	public static function aiml_dir()
	{
		return dirname(dirname(__FILE__)).'/aiml/';
	}
	
	
	public static function option($in_key, $in_default = null)
	{
		if (isset(JxBotConfig::$config[$in_key])) return JxBotConfig::$config[$in_key];
		return $in_default;
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
		try
		{
			$stmt = JxBotDB::$db->prepare('SELECT opt_key, opt_value FROM opt');
			$stmt->execute();
			$rows = $stmt->fetchAll(PDO::FETCH_NUM);
			if (count($rows) == 0) throw new Exception('Not properly installed.');
			foreach ($rows as $row)
			{
				JxBotConfig::$config[$row[0]] = $row[1];
			}
		}
		catch (Exception $err) 
		{
			JxBotConfig::run_installer();
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
	// deprecated; use predicate
	{
		return JxBotConfig::option('bot_'.strtolower($in_key)); // to be improved & cached, etc.!  ***
	}
	
	public static function predicate($in_name)
	{
		return JxBotConfig::option('bot_'.strtolower($in_name)); // to be improved & cached, etc.!  ***
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
			if ($row[0] === 'bot_birthday') continue;
			if ($row[0] === 'bot_age') continue;
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
	
	
	public static function widget_timezone()
	{
?>
<select name="bot_tz" id="bot_tz">
<option value=""></option>
<?php
$timezone_identifiers = DateTimeZone::listIdentifiers();
foreach ($timezone_identifiers as $tz)
{
	print '<option value="'.$tz.'" '.(JxBotConfig::option('bot_tz') == $tz ? ' selected="true"' : '').'>'.$tz.'</option>';
}
?>
</select>
<?php
	}
}









