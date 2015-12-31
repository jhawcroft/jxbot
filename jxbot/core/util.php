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


