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
	
	/* complexity/scale;
	   How many categories of interaction does the system accomodate? */
	$stmt = JxBotDB::$db->prepare('SELECT COUNT(*) FROM category');
	$stmt->execute();
	$metrics['category_count'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	/* complexity/scale;
	   How many distinct patterns does the system match against user input? */
	$stmt = JxBotDB::$db->prepare('SELECT COUNT(*) FROM pattern');
	$stmt->execute();
	$metrics['pattern_count'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	/* complexity/scale;
	   How many distinct responses can the system provide? */
	$stmt = JxBotDB::$db->prepare('SELECT COUNT(*) FROM template');
	$stmt->execute();
	$metrics['template_count'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	
	$stmt = JxBotDB::$db->prepare('SELECT COUNT(DISTINCT session) FROM log
		WHERE stamp >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)');
	$stmt->execute();
	$metrics['active_clients'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	
	$stmt = JxBotDB::$db->prepare('SELECT COUNT(DISTINCT session) FROM log
		WHERE stamp >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)');
	$stmt->execute();
	$metrics['clients_5min'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	
	$stmt = JxBotDB::$db->prepare('SELECT COUNT(DISTINCT session) FROM log
		WHERE stamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)');
	$stmt->execute();
	$metrics['clients_hour'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	
	$stmt = JxBotDB::$db->prepare('SELECT COUNT(DISTINCT session) FROM log
		WHERE stamp >= DATE_SUB(NOW(), INTERVAL 1 DAY)');
	$stmt->execute();
	$metrics['clients_day'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	
	$stmt = JxBotDB::$db->prepare('SELECT COUNT(DISTINCT session) FROM log
		WHERE stamp >= DATE_SUB(NOW(), INTERVAL 1 WEEK)');
	$stmt->execute();
	$metrics['clients_week'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	
	$stmt = JxBotDB::$db->prepare('SELECT COUNT(DISTINCT session) FROM log
		WHERE stamp >= DATE_SUB(NOW(), INTERVAL 1 MONTH)');
	$stmt->execute();
	$metrics['clients_month'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	
	$stmt = JxBotDB::$db->prepare('SELECT AVG(time_respond) FROM log
		WHERE time_respond IS NOT NULL AND
		stamp >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)');
	$stmt->execute();
	$metrics['active_response'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	
	/* active load;
	   How well is the system coping with the load;
	   ie. is the matching & templating logic maintaining acceptably small
	   response times? */
	$stmt = JxBotDB::$db->prepare('SELECT MAX(time_respond - time_service) FROM log
		WHERE stamp >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)');
	$stmt->execute();
	$metrics['active_load'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	/* active performance:
	   How well is the chat system performing at the present moment;
	   user perceived response time */
	$stmt = JxBotDB::$db->prepare('SELECT MAX(time_respond) FROM log
		WHERE stamp >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)');
	$stmt->execute();
	$metrics['active_max_response'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	/* active performance:
	   How relivant / intelligent is the response;
	   based upon word-wildcard ratio and pattern length */
	$stmt = JxBotDB::$db->prepare('SELECT AVG(avg_word_wild_ratio) FROM log
		WHERE stamp >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)');
	$stmt->execute();
	$metrics['active_resp_intel'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	
	// load:
	// average response time whenever it's had periods of interaction 
	// and most recently
	
	// obviously if it hasn't had interaction for a minute or more,
	// load should show as zero
	
	// if interaction is current,
	// within the last minute, we want average response time
	// and number of users
	
	// should we use maximum or average ? or both?
	
	// worst case seems reasonable
	
	// 1. Stat average of the most recent periods of activity (more than a couple 
	// of interactions) - rationale, is the system performing well when it's performing?
	
	// this could be extended to graph when the system is performing well and when it's not
	// wherein, not servicing requests at all is considered good performance too
	
	// also be interesting to track the number of requests for a given user 
	// and repeat users, along with how many times they return
	
	//  2. active load - ? 1 -minute window,NOW, maximum response time - external service requests
	
	// consider these metrics in-terms of some established UI guidance on load times
	// for websites.
	// and also with respect to human chat and typing speeds; which does allow some room
	// particularly if the interface shows the 'computer as typing'.
	
	// normal website:
	//eg. 0.1 second ideal - green
	// 1.0 second limit of good performance faded green?
	// 1.1-3.0 seconds feels slow - yellow-orange
	// 3.1-6.0 seconds - feels very slow - orange-red
	//6.1 + really poor and indicates server issues anyway
	// 8 seconds - lost client - black
	
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
<tr>
	<td>Active Clients</td>
	<td><?php print $metrics['active_clients']; ?></td>
</tr>
<tr>
	<td>Clients</td>
	<td><?php print $metrics['clients_5min']; ?> (last 5 minutes)</td>
</tr>
<tr>
	<td>Clients</td>
	<td><?php print $metrics['clients_hour']; ?> (last hour)</td>
</tr>
<tr>
	<td>Clients</td>
	<td><?php print $metrics['clients_day']; ?> (last 24 hours)</td>
</tr>
<tr>
	<td>Clients</td>
	<td><?php print $metrics['clients_week']; ?> (last 7 days)</td>
</tr>
<tr>
	<td>Clients</td>
	<td><?php print $metrics['clients_month']; ?> (last 30 days)</td>
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

$result = JxBotAiml::parse_template('

<condition name="age">
	<li value="30">Really?</li>
	<li value="31">You\'re 31!</li>
	<li>Default</li>
</condition>


');


print '<pre>';
print $result->generate(null);
print '</pre>';*/

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




