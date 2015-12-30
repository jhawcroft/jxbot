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



function compute_metrics()
{
	$metrics = array();

/********************************************************************************
Bot Statistics
*/

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
	
	/* complexity/scale;
	   How many distinct words are *recognised* within the bot vocabulary? */
	$stmt = JxBotDB::$db->prepare('SELECT COUNT(*) FROM word');
	$stmt->execute();
	$metrics['recognised_vocab'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	/* general;
	   How many interactions has the bot had over it's lifetime? */
	$stmt = JxBotDB::$db->prepare('SELECT interactions FROM stats');
	$stmt->execute();
	$metrics['interaction_count'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	// in future, could also include words in templates as a separate figure
	// ie. total vocabulary
	
/********************************************************************************
Active Load and Performance
*/

	/* active load;
	   How many clients are currently in conversaton with the bot? */
	$stmt = JxBotDB::$db->prepare('SELECT COUNT(DISTINCT session) FROM log
		WHERE stamp >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)');
	$stmt->execute();
	$metrics['active_clients'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	/* active load;
	   What is the total average response time for active clients? */
	$stmt = JxBotDB::$db->prepare('SELECT AVG(time_respond) FROM log
		WHERE stamp >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)');
	$stmt->execute();
	$metrics['active_response'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	/* active load;
	   How well is the system coping with the load;
	   ie. is the matching & templating logic maintaining acceptably small
	   response times? */
	$max_sys_time = 0.1; // maximum time in seconds the core system can take to respond acceptably
	$stmt = JxBotDB::$db->prepare('SELECT MAX(time_respond - time_service) FROM log
		WHERE stamp >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)');
	$stmt->execute();
	$raw = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	if ($raw === null) $raw = 0;
	$load = $raw / ($max_sys_time * 0.8);
	$metrics['active_load'] = $load;
	unset($raw);
	
	/* active performance:
	   How well is the chat system performing at the present moment;
	   user perceived response time (worst case) */
	$stmt = JxBotDB::$db->prepare('SELECT MAX(time_respond) FROM log
		WHERE stamp >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)');
	$stmt->execute();
	$metrics['active_max_response'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	/* active performance:
	   How relivant / intelligent is the response;
	   based upon word-wildcard ratio and pattern length (ie. pattern specificity) */
	$percent_best_is_good = 0.51;
	$stmt = JxBotDB::$db->prepare('SELECT MAX(intel_score) FROM log');
	$stmt->execute();
	$max_score = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	$stmt = JxBotDB::$db->prepare('SELECT AVG(intel_score) FROM log
		WHERE stamp >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)');
	$stmt->execute();
	$raw = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	$score = $raw / ($max_score * $percent_best_is_good);
	// a score of 1.0 is good
	// a score of 0.5 is ordinary
	// a score of less is mediocre
	// a score of 1.5 is very good
	// a score of 2+ is excellent
	
	// translate to human type 'IQ', with 100 being average, 150 being very smart, etc.
	$metrics['active_resp_intel'] = $score * 100;
	

	
/********************************************************************************
Historical and Trends
*/

// ie. when the system has been in conversation over various periods:
// hour, day, week, month, year
// how well has it performed and handled load

// load v performance
// load refers to how well the system is coping with the shear number of users
// currently interacting with it.
// performance refers to how well the system is acting as a chat robot
// (which is related to load, but not the same)
// for example, how intelligent the conversation is, is also a valid performance
// metric.

// repeat clients & number of client interactions:
// how many interactions on average are had with any given user (in total,
// and for a given conversation)
// are users returning, and how many separate conversations are they having?
// (conversation breaks are defined to be >= 15-minutes)


	
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
	
	
	
// find some established guidance on UI response times for
// websites
// and chat systems	

	// eg. normal website:
	// 0.1 second ideal - green
	// 1.0 second limit of good performance faded green?
	// 1.1-3.0 seconds feels slow - yellow-orange
	// 3.1-6.0 seconds - feels very slow - orange-red
	//6.1 + really poor and indicates server issues anyway
	// 8 seconds - lost client - black
	//http://www.loadtestingtool.com/help/response-time.shtml
	
// consideration given to awkward silence?!
// eg. http://healthland.time.com/2010/12/30/awkward-silences-4-seconds-is-all-it-takes-to-feel-rejected/
// 4 seconds maximum
	
	return $metrics;
}

$metrics = compute_metrics();




?>


<div class="dashboard-widget">
<h2>Bot Statistics</h2>

	<table id="dashboard-general">
	<tr>
		<td>Interaction Categories</td>
		<td><?php print $metrics['category_count']; ?></td>
	</tr>
	<tr>
		<td>Input Patterns</td>
		<td><?php print $metrics['pattern_count']; ?></td>
	</tr>
	<tr>
		<td>Output Templates</td>
		<td><?php print $metrics['template_count']; ?></td>
	</tr>
	<tr>
		<td>Recognised Vocabulary</td>
		<td><?php print $metrics['recognised_vocab']; ?></td>
	</tr>
	<tr>
		<td>Lifetime Interactions</td>
		<td><?php print $metrics['interaction_count']; ?></td>
	</tr>
	</table>
</div>


<div class="dashboard-widget">
<h2>Active Load</h2>

<p><?php JxWidget::dynamic_meter(200, $metrics['active_load']); ?></p>

	<table>
	<tr>
		<td>Current Clients</td>
		<td><?php print $metrics['active_clients']; ?></td>
	</tr>
	<tr>
		<td>Average Response Time</td>
		<td><?php 
		
		if ($metrics['active_clients'] > 0)
			print number_format($metrics['active_response'], 3).' seconds'; 
		else
			print '-';
			
		?></td>
	</tr>
	<tr>
		<td>Bot Load</td>
		<td><?php print number_format($metrics['active_load'], 2); ?> %</td>
	</tr>
	</table>
</div>


<div class="dashboard-widget">
<h2>Active Performance</h2>

	<table>
	<tr>
		<td>Worst Response Time</td>
		<td><?php 
		
		if ($metrics['active_clients'] > 0)
			print number_format($metrics['active_max_response'], 3).' seconds'; 
		else
			print '-';
			
		?></td>
	</tr>
	<tr>
		<td>Response IQ</td>
		<td><?php 
		
		if ($metrics['active_clients'] > 0)
			print number_format($metrics['active_resp_intel'], 0); 
		else
			print '-';
			
		?></td>
	</tr>
	</table>
</div>


<div class="clear"></div>





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


<script type="text/javascript">
/*
window.setTimeout(function() {
	location.reload();
}, 60000);
*/
</script>


