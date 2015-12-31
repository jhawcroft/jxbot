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

/* client include for JxBot */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(dirname(__FILE__).'/jxbot.php');


class JxBotClient
{
	public static function init()
	{
		JxBot::init_client();
	}

	
	public static function is_available()
	{
		return JxBotConverse::bot_available();
	}
	
	
	public static function resume_conversation($in_id = null)
	{
		if (!JxBotConverse::bot_available()) return $in_id;
	
		if ($in_id === null)
		{
			if (isset($_COOKIE['jxbot-client']))
				$in_id = $_COOKIE['jxbot-client'];
		}
	
		$out_id = JxBotConverse::resume_conversation( $in_id );
		
		if (($in_id === null) || ($in_id != $out_id))
		{
			setcookie('jxbot-client', $out_id, 0);
		}
		
		return $out_id;
	}
	
	
	public static function respond($in_input)
	{
		if (trim($in_input) == '')
			return JxBotConverse::get_greeting();
		else
			return JxBotConverse::get_response($in_input);
	}
}





