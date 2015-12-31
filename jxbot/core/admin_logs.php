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

if (!defined('JXBOT_ADMIN')) die('Direct script access not permitted.');


function purge_old_logs()
{
	JxBotDB::$db->exec('DELETE FROM log WHERE stamp < DATE_SUB(NOW(), INTERVAL 1 MONTH)');
	
	JxBotDB::$db->exec('DELETE FROM session 
		WHERE accessed < DATE_SUB(NOW(), INTERVAL 1 MONTH) AND convo_id != \'admin\';');
}


?>

<div class="right" id="right-nav">

<h2>Recent Conversations</h2>

<?php
if (isset($_REQUEST['purge-old']))
	purge_old_logs();
?>
<p><button type="submit" name="purge-old" value="month">Purge Old Log Entries</button></p>


<?php
$sessions = JxBotConverse::latest_sessions();
JxWidget::grid(array(
	array('id'=>0, 'visible'=>false, 'key'=>true),
	array('id'=>1, 'label'=>'Client', 'link'=>'?page=chat&subpage=logs&convo=$$')
), $sessions);
?>


</div>

<div id="centre-content">
<h2>Transcript</h2>

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

<?php
if (!isset($_REQUEST['convo'])) {
?>
<p>Select a recent conversation on the right to view the transcript.</p>
<?php
} else {

	$stmt = JxBotDB::$db->prepare('SELECT stamp,input,output FROM log WHERE session=? ORDER BY id DESC');
	$stmt->execute(array($_REQUEST['convo']));
	$rows = $stmt->fetchAll(PDO::FETCH_NUM);

	if (count($rows) == 0) print '<p>No data to display.</p>';
	else
	{
		print '<div class="log">';
		foreach ($rows as $row)
		{
			//print $row[0].'<br>';
			
			print '<strong><span class="log-bl">Bot:</span> '.$row[2].'</strong><br>';
			if ($row[1] !== '') 
				print '<span class="log-cl">Client:</span> '.$row[1].'<br>';
		}
		print '</div>';
	}

}
?>




</div>