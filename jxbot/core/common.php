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
 

function jxbot_config()
{
	global $jxbot_config, $jxbot_db;
	$jxbot_config = array();

	require_once('defaults.php');
	require_once('util.php');
	require_once('widget.php');
	require_once('db.php');
	
	$jxbot_config['config_dir'] = dirname(dirname(__FILE__)).'/';
	
	$config_file = $jxbot_config['config_dir'] . 'config.php';
	if (!is_readable($config_file))
	{
		require_once('install.php');
		exit;
	}
	
	require_once($config_file);
	
	if (!isset($jxbot_config['bot_url']))
		jxbot_die("Bot configuraton is missing bot_url.");
	
	if (isset($jxbot_config['debug']) && $jxbot_config['debug'])
	{
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
	}
	
	jxbot_connect_db() or jxbot_die("Couldn't connect to database.");
	
	if (!jxbot_is_installed())
	{
		require_once('install.php');
		exit;
	}
	
	BotDefaults::load_configuration();  // timezone, etc.
	
	
	if (isset($jxbot_config['timezone']))
		date_default_timezone_set($jxbot_config['timezone']);
	
	
	require_once('nl.php');
	require_once('nl-aux.php');
	require_once('converse.php');
}


function jxbot_start_session()
{
	session_id(BotDefaults::SESSION_NAME);
	session_start();
}


function jxbot_finish_session()
{
	session_write_close();
}


jxbot_config();







