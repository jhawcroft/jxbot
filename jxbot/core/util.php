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

/* general utilities */

if (!defined('JXBOT')) die('Direct script access not permitted.');



function jxbot_die($in_error)
/* something really bad has happened, print an error message and end the HTTP request */
{
	print $in_error;
	exit;
}



// am I still using this?
if (!function_exists("array_column"))
{
    function array_column($array, $column_name)
    {
        return array_map(function($element) use($column_name) {
        	return $element[$column_name];
        }, $array);
    }
}


class JxBotUtil
{
	public static function phpinfo()
	/* outputs PHP info, wrapped in an appropriate div for custom styling;
	used within the administration interface */
	{
		ob_start();
		phpinfo();
		$pinfo = ob_get_contents();
		ob_end_clean();

		$pinfo = preg_replace( '%^.*<body>(.*)</body>.*$%ms','$1',$pinfo);
		echo '<div id="phpinfo">'.$pinfo.'</div>';
	}
	
	
	public static function request_url()
	/* safely returns the request URL for the bot - minus any query string */
	{
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off')
			$protocol = 'https';
		else $protocol = 'http';
		return $protocol . '://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER["REQUEST_URI"],'?');
	}
	
	
	public static function inputs($in_names)
	/* retrieves the specified list of POST/GET variables as an associative array,
	wherein all specified names are present within the result regardless of whether a 
	value was submitted in this HTTP request; keys for which no value was submitted
	will be associated with NULL */
	{
		if (is_string($in_names)) $in_names = explode(',', $in_names);
		$result = array();
		foreach ($in_names as $name)
		{
			if (isset($_REQUEST[$name])) $result[$name] = $_REQUEST[$name];
			else $result[$name] = null;
		}
		return $result;
	}
	
}


