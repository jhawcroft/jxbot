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

<div class="right" id="right-nav">

<h2>Recent Conversations</h2>

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