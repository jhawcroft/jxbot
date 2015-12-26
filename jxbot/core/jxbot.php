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

require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/util.php');
require_once(dirname(__FILE__).'/db.php');
require_once(dirname(__FILE__).'/nl.php');
require_once(dirname(__FILE__).'/catman.php');
require_once(dirname(__FILE__).'/generate.php');
require_once(dirname(__FILE__).'/engine.php');
require_once(dirname(__FILE__).'/converse.php');
require_once(dirname(__FILE__).'/aiml.php');
require_once(dirname(__FILE__).'/widget.php');



class JxBot
{
	const VERSION = '0.9';

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








