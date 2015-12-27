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



// count size of dictionary
// count number of categories
// count number of distinct sequences (patterns)
// count number of templates

// eventually provide some idea of load/performance (keep track of response times
// and number of almost simultaneous sessions)

function compute_metrics()
{
	$metrics = array();
	
	/*$stmt = JxBotDB::$db->prepare('SELECT COUNT(*) FROM word');
	$stmt->execute();
	$metrics['dictionary_size'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];*/
	
	$stmt = JxBotDB::$db->prepare('SELECT COUNT(*) FROM category');
	$stmt->execute();
	$metrics['category_count'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	$stmt = JxBotDB::$db->prepare('SELECT COUNT(*) FROM pattern');
	$stmt->execute();
	$metrics['pattern_count'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	$stmt = JxBotDB::$db->prepare('SELECT COUNT(*) FROM template');
	$stmt->execute();
	$metrics['template_count'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	return $metrics;
}

$metrics = compute_metrics();




?>

<style type="text/css">
table.dashboard-stats td:first-child
{
	width: 10em;
}
</style>


<table class="dashboard-stats">
<tr>
	<td>Categories</td>
	<td><?php print $metrics['category_count']; ?></td>
</tr>
<tr>
	<td>Patterns</td>
	<td><?php print $metrics['pattern_count']; ?></td>
</tr>
<tr>
	<td>Templates</td>
	<td><?php print $metrics['template_count']; ?></td>
</tr>
<!--<tr>
	<td>Dictionary Size</td>
	<td><?php print $metrics['dictionary_size']; ?></td>
</tr>-->

</table>



<!--

Possible performance statistics:

Load:  queries per quanta - maybe 5 seconds?
	   related to dynamically adjusting assessment of response times on the server
	   (DB + web server combined)
	   
	   have a hard-coded baseline which we compute by trial and error, which is an acceptable
	   speed of processing probably on a local machine, with no load,
	   
	   also a baseline on a machine which is shared, like the netregistry box
	   
	   and an arbitrary figure which represents verging on unacceptable response times
	   
	   track the average queries per 5-seconds,
	   and the average queries per 10-seconds, 30-seconds, minute, so as to 
	   provide some kind of figure when really low
	   
	   track the average response time for an interval representing unacceptable resposne time ?
	   
	   compare and extrapolate these figures against the various baselines to compute a usage figure
	
	   consider looking up load computation algorithms;
	   also consider what MySQL might be able to give
	   
Raw Response Times: simpler!

Cache Hits: <when we have a cache!>; ratio hits vs misses
		also track cached response times vs non-cached response times

Knowledge base, top hits?

History: graph over last 24 hours
and last week, with spikes in usage

Status:  is the system online for the public?	   

-->

<?php

//testing
/*
JxBotConverse::resume_conversation('admin');

$result = JxBotAiml::parse_template('Hello <star index="1"/>, how are you?
<random>
  <li>My name is <bot><name>name</name></bot></li>
  <li>I am <bot name="name"/></li>
</random>. Today is <date/>! Test result: <program/>.  Upper: <formal>SomE sTuff.</formal>

<condition value="orange"><name>favoritecolor</name>You like the same color as me!</condition>


<condition name="age">
	<li value="30">You\'re 31!</li>
	<li value="31">Really?</li>
	<li>Default</li>
</condition>

<condition>
	<li name="age" value="30">You\'re 31!</li>
	<li value="31"><name>age</name>Really?</li>
	<li>Default</li>
</condition>


');
*/
/*
<random>
  <li>My name is <bot><name>botname</name></bot></li>
  <li>I am <bot name="botname"/></li>
</random>
*/

/*
print '<pre>';
var_dump($result);
print '</pre>';*/

//print '<pre>';
//print $result->generate(5);
//print '</pre>';

?>




