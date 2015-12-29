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

/* the bot administration pages */

if (!defined('JXBOT')) die('Direct script access not permitted.');




class JxBotAjax
{

	private static function set_file_status($in_name, $in_status)
	{
		try
		{
			$stmt = JxBotDB::$db->prepare('INSERT INTO file (name, status) VALUES (?, ?)');
			$stmt->execute(array( $in_name, $in_status ));
		}
		catch (Exception $err) {} // already in the table
		
		$stmt = JxBotDB::$db->prepare('UPDATE file SET status=? WHERE name=?');
		$stmt->execute(array( $in_status, $in_name ));
	}


	public static function load()
	{
		$inputs = JxBotUtil::inputs('file');
		$file = JxBotConfig::aiml_dir() . str_replace(array('/', './', '../'), '', $inputs['file']);
	
		//print $file;
	
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		header("Content-type: text/plain; charset=utf-8");

	
		$importer = new JxBotAimlImport();
		$result = $importer->import($file);
		if (is_array($result)) // success
		{
			JxBotAjax::set_file_status($inputs['file'], 'Loaded');
			
			print "DONE\n";
			foreach ($result as $notice)
			{
				print $notice."\n";
			}
		}
		else // error
		{
			JxBotAjax::set_file_status($inputs['file'], 'Load Error');
			
			print 'ERROR '.$result."\n";
		}
	}
	
	

}




