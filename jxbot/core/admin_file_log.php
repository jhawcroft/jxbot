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

if (!defined('JXBOT_ADMIN')) die('Direct script access not permitted.');


?>


<style type="text/css">
.log
{
	line-height: 1.7em;
}
.log-cl
{
	display: inline-block;
	width: 5em;
}
.log-bl
{
	display: inline-block;
	width: 5em;
}
</style>


<div id="centre-content" style="float: left; max-width: 85%; margin-right: 2 em;">
<?php


$inputs = JxBotUtil::inputs('action');
if ($inputs['action'] == 'purge-log')
{
	JxBotDB::$db->exec('TRUNCATE aiml_log');
}


JxWidget::hidden('subpage', 'logs');

$stmt = JxBotDB::$db->prepare('SELECT level,message,file,stamp FROM aiml_log ORDER BY id DESC');
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_NUM);

if (count($rows) == 0) print '<p>No data to display.</p>';
else
{
	print '<div class="log">';
	foreach ($rows as $row)
	{
		switch ($row[0])
		{
		case 1: print 'Notice: '; break;
		case 2: print 'Warning: '; break;
		case 3: print 'Error: '; break;
		}
		
		if ($row[2] != '') print $row[2].': ';
		print $row[1];
		
		print '<br>';
	}
	print '</div>';
}

?>
</div>


<div id="right-nav" style="float: left;">
<button type="submit" name="action" value="purge-log">Purge Log</button>
</div>



<div class="clear"></div>

