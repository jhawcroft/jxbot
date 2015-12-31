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

/* main include for JxBot;
there are two principal entry modes: i) client chat, ii) administration */

define('JXBOT', 1);


require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/util.php');
require_once(dirname(__FILE__).'/db.php');
require_once(dirname(__FILE__).'/nl.php');
require_once(dirname(__FILE__).'/sentence-en.php');
require_once(dirname(__FILE__).'/catman.php');
require_once(dirname(__FILE__).'/generate.php');
require_once(dirname(__FILE__).'/engine.php');
require_once(dirname(__FILE__).'/converse.php');
require_once(dirname(__FILE__).'/aiml.php');
require_once(dirname(__FILE__).'/widget.php');
require_once(dirname(__FILE__).'/async_loader.php');
require_once(dirname(__FILE__).'/exclusion.php');



class JxBot
{
	const VERSION = '0.91';
	const PROGRAM = 'JxBot';

	private $config = array();
	
	
	public static function fatal_error($in_error)
	{
		print $in_error; // should be improved **
		exit;
	}
	

	public static function run_admin()
	{
		JxBotConfig::setup_environment();
		
		require_once(dirname(__FILE__).'/admin.php');
		
		session_name(JxBotConfig::SESSION_NAME);
		session_start();
		
		JxBotAdmin::admin_generate();
		
	}
	
	
	public static function start_session()
	{
		session_name(JxBotConfig::SESSION_NAME);
		session_start();
		
		return session_id();
	}
	
	
	public static function init_client()
	{
		JxBotConfig::setup_environment();
	}
}








