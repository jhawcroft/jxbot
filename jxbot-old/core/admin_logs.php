
<div class="right" id="right-nav">

<h2>Recent Conversations</h2>

<?php
$stmt = JxBotDB::$db->prepare('SELECT DISTINCT convo_id FROM log ORDER BY id desc LIMIT 50');
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_NUM);

JxWidget::grid(array(
	array('id'=>0, 'label'=>'Identifier', 'key'=>true, 'link'=>'?page=logs&convo=$$')
), $rows);
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

	$stmt = JxBotDB::$db->prepare('SELECT stamp,input,output FROM log WHERE convo_id=? ORDER BY id ASC');
	$stmt->execute(array($_REQUEST['convo']));
	$rows = $stmt->fetchAll(PDO::FETCH_NUM);

	if (count($rows) == 0) print '<p>No data to display.</p>';
	else
	{
		print '<div class="log">';
		foreach ($rows as $row)
		{
			//print $row[0].'<br>';
			if ($row[1] !== '') 
				print '<span class="log-cl">Client:</span> '.$row[1].'<br>';
			print '<strong><span class="log-bl">Bot:</span> '.$row[2].'</strong><br>';
		}
		print '</div>';
	}

}
?>




</div>