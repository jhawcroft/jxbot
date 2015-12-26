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
		$options = array(
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
		); 
	
		try
		{
			/* inititate the connection */
			JxBotDB::$db = new PDO($dsn, $user, $password, $options);	
			if (JxBotDB::$db === false) 
				throw new Exception("Couldn't connect to database.");
			
			/* ensure all further accesses throw an exception 
			in the event of an error */
			JxBotDB::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (Exception $err)
		{
			/* if anything goes wrong, return false */
			return false;
		}
	
		return true; /* successful connection! */
	}
}





