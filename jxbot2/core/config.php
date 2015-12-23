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
	}
}









