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
/* connection to the MySQL database */

if (!defined('JXBOT')) die('Direct script access not permitted.');


class JxBotDB
{
	public static $db = NULL;
	public static $prefix = '';
	
	
	function is_installed()
	/* minimal check to see if the database schema is present,
	ie. the database has been installed */
	{
		try
		{
			$stmt = JxBotDB::$db->prepare('SELECT * FROM category LIMIT 1');
			$stmt->execute();
			return true;
		}
		catch (Exception $err)
		{}
		return false;
	}
 

	public static function connect($host, $name, $prefix, $user, $password)
	/* establishes a connection to the specified database */
	{
		/* define the connection parameters */
		$dsn = 'mysql:host='.$host.';dbname='.$name;
	
		try
		{
			/* inititate the connection */
			JxBotDB::$db = new PDO($dsn, $user, $password);	
			if (JxBotDB::$db === false) 
				throw new Exception("Couldn't connect to database.");
			
			/* ensure all further accesses throw an exception 
			in the event of an error */
			JxBotDB::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			/* set character set */
			JxBotDB::$db->exec('SET NAMES utf8');
		}
		catch (Exception $err)
		{
			/* if anything goes wrong, return false */
			return false;
		}
	
		return true; /* successful connection! */
	}
}





