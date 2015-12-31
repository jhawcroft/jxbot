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

<h2>Security Log</h2>


<?php


$inputs = JxBotUtil::inputs('action');
if ($inputs['action'] == 'purge-log')
{
	JxBotDB::$db->exec('TRUNCATE login');
}


JxWidget::hidden('subpage', 'logs');

$stmt = JxBotDB::$db->prepare('SELECT note,username,stamp FROM login ORDER BY id DESC');
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_NUM);

if (count($rows) == 0) print '<p>No data to display.</p>';
else
{
	print '<div class="log">';
	foreach ($rows as $row)
	{
		print $row[0].' '.$row[1];
		
		print '<br>';
	}
	print '</div>';
}

?>
</div>


<div id="right-nav" style="float: left;">
<button type="submit" name="action" value="purge-log">Purge Security Log</button>
</div>



<div class="clear"></div>

