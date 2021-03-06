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

/* asyncronous AIML loader */

if (!defined('JXBOT')) die('Direct script access not permitted.');




class JxBotAsyncLoader
{
	const LOG_LEVEL_ERROR = 3;
	const LOG_LEVEL_WARNING = 2;
	const LOG_LEVEL_NOTICE = 1;
	

	public static function log($in_level, $in_detail, $in_file)
	{
		$stmt = JxBotDB::$db->prepare('INSERT INTO aiml_log (file, message, level) VALUES (?, ?, ?)');
		$stmt->execute(array( $in_file, substr($in_detail, 0, 255), $in_level ));
	}
	
	
	public static function set_file_status($in_file, $in_status)
	{
		$stmt = JxBotDB::$db->prepare('UPDATE file SET status=? WHERE name=?');
		$stmt->execute(array( $in_status, $in_file ));
	}
	
	
	private static function detatch_http_request()
	{
		/*ignore_user_abort(true);
		session_write_close();
		header("Content-Encoding: none");
		header("Content-Length: ".ob_get_length());
		header("Connection: close");
		ob_end_flush();
		flush();*/
	}
	
	
	public static function schedule_all()
	{
		$stmt = JxBotDB::$db->exec('UPDATE file SET status=\'Scheduled\' 
			WHERE status != \'Loaded\' AND status NOT LIKE \'Loading%\'');
	}
	
	
	public static function stop_loading()
	{
		$stmt = JxBotDB::$db->exec('UPDATE file SET status=\'Load Aborted\' 
			WHERE status = \'Scheduled\'');
	}
	
	
	public static function toggle_file($in_file)
	{
		$stmt = JxBotDB::$db->prepare('SELECT status FROM file WHERE name=?');
		$stmt->execute(array($in_file));
		$row = $stmt->fetchAll(PDO::FETCH_NUM);
		if (count($row) == 0) return;
		$status = $row[0][0];
		
		if ($status == 'Scheduled')
		{
			$stmt = JxBotDB::$db->prepare('UPDATE file SET status=\'Load Aborted\' 
			WHERE name=? AND status = \'Scheduled\'');
			$stmt->execute(array($in_file));
		}
		else
		{
			$stmt = JxBotDB::$db->prepare('UPDATE file SET status=\'Scheduled\' 
				WHERE name=? AND status != \'Loaded\' AND status NOT LIKE \'Loading%\'');
			$stmt->execute(array($in_file));
		
		}
	}


	public static function process_scheduled()
	/* attempts to import all the scheduled AIML files;
	will automatically  */
	{
		/* detatch from invoking HTTP process so we can't be interrupted */
		JxBotAsyncLoader::detatch_http_request();
	
		/* ensure we are the only instance running this process */
		try
		{
			if (!JxBotExclusion::get_exclusive()) return;
		}
		catch (Exception $err)
		{
			JxBotAsyncLoader::log(JxBotAsyncLoader::LOG_LEVEL_ERROR, $err->getMessage(), '');
			return;
		}
		
		/* iterate through all scheduled files */
		while (true)
		{
			$stmt = JxBotDB::$db->prepare('SELECT name FROM file WHERE status = \'Scheduled\' ORDER BY name LIMIT 1');
			$stmt->execute();
			$next = $stmt->fetchAll(PDO::FETCH_NUM);
			if (count($next) == 0) return; /* we're done */
			$file = $next[0][0];
		
			/* flag the file to indicate we're processing it, and prevent a loop if something is amiss */
			JxBotAsyncLoader::set_file_status($file, 'Loading');
			
			/* does the file actually exist? */
			$path = JxBotConfig::aiml_dir() . $file;
			if (!file_exists($path))
			{
				JxBotAsyncLoader::set_file_status($file, 'Not Available');
				continue;
			}
			
			/* run the AIML importer */
			$importer = new JxBotAimlImport();
			$result = $importer->import($path);
			
			// ! TODO:  The notices, warnings and errors of this mechanism should be
			//          sent to us via our log() method, not passed back in an array. **
			
			/* check for errors and notices */
			if (is_array($result)) // success
			{
				JxBotAsyncLoader::set_file_status($file, 'Loaded');
				JxBotAsyncLoader::log(JxBotAsyncLoader::LOG_LEVEL_NOTICE, 'Loaded.', $file);
				
				/* log the results */
				foreach($result as $notice)
					JxBotAsyncLoader::log(JxBotAsyncLoader::LOG_LEVEL_WARNING, $notice, $file);
			
			}
			else // error
			{
				JxBotAsyncLoader::set_file_status($file, 'Load Error');
				JxBotAsyncLoader::log(JxBotAsyncLoader::LOG_LEVEL_ERROR, $result, $file);
			}
		}
	}	

}




