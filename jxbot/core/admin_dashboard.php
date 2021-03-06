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
	$divisor = ($max_score * $percent_best_is_good);
	if ($divisor == 0) $score = 1;
	else $score = $raw / $divisor;
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

/*
Performance
*/

	/* average client interactions */
	$stmt = JxBotDB::$db->prepare('SELECT AVG(intr), AVG(conv_l), MIN(conv_l), MAX(conv_l) FROM 
(SELECT log.session, COUNT(log.id) AS intr, (MAX(UNIX_TIMESTAMP(log.stamp)) - MIN(UNIX_TIMESTAMP(log.stamp)))/60 as conv_l
FROM log JOIN session ON log.session=session.id 
WHERE session.convo_id != \'admin\' 
GROUP BY session) AS int_data;');
	$stmt->execute();
	$raw = $stmt->fetchAll(PDO::FETCH_NUM)[0];
	$metrics['hist_avg_client_intr'] = intval( $raw[0] );
	$metrics['hist_avg_convo_time'] = intval( $raw[1] );
	$metrics['hist_min_convo_time'] = intval( $raw[2] );
	$metrics['hist_max_convo_time'] = intval( $raw[3] );

	/* average response time */
	$stmt = JxBotDB::$db->prepare('SELECT AVG(time_respond) FROM log');
	$stmt->execute();
	$metrics['hist_avg_response'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	/* worst response time */
	$stmt = JxBotDB::$db->prepare('SELECT MAX(time_respond) FROM log');
	$stmt->execute();
	$metrics['hist_max_response'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	/* average response IQ */
	// turn this into a function, as it's complicated and used twice in variation ** ! TODO
	$stmt = JxBotDB::$db->prepare('SELECT MAX(intel_score) FROM log');
	$stmt->execute();
	$max_score = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	$stmt = JxBotDB::$db->prepare('SELECT AVG(intel_score) FROM log');
	$stmt->execute();
	$raw = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	$divisor = ($max_score * $percent_best_is_good);
	if ($divisor == 0) $score = 1;
	else $score = $raw / $divisor;
	$metrics['hist_iq_response'] = $score * 100;
	

/*
Load
*/

	$stmt = JxBotDB::$db->prepare('SELECT AVG(active), MAX(active) FROM (SELECT COUNT(DISTINCT session) AS active FROM log GROUP BY UNIX_TIMESTAMP(stamp) DIV 60) AS act_dat');
	$stmt->execute();
	$raw = $stmt->fetchAll(PDO::FETCH_NUM)[0];
	$metrics['hist_avg_clients'] = $raw[0];
	$metrics['hist_max_clients'] = $raw[1];


	$stmt = JxBotDB::$db->prepare('SELECT AVG(time_respond - time_service) FROM log');
	$stmt->execute();
	$raw = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	if ($raw === null) $raw = 0;
	$load = $raw / ($max_sys_time * 0.8);
	$metrics['hist_load'] = $load;
	
	
	
	
	

/*
	
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
	
	*/
	
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

	<table class="dashboard-stats">
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

	<table class="dashboard-stats">
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

	<table class="dashboard-stats">
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


<div class="clear" style="height: 2em;"></div>


<div class="dashboard-widget">
<h2>General Performance</h2>

	<table class="dashboard-stats">
	<tr>
		<td>Average Interactions</td>
		<td><?php 
		
		if ($metrics['hist_avg_client_intr'] > 0)
			print number_format($metrics['hist_avg_client_intr'], 0); 
		else
			print '-';
			
		?></td>
	</tr>
	<tr>
		<td>Average Conversation</td>
		<td><?php 
		
		if ($metrics['hist_avg_response'] > 0)
			print number_format($metrics['hist_avg_convo_time'], 1).' minutes'; 
		else
			print '-';
			
		?></td>
	</tr>
	<tr>
		<td>Shortest Conversation</td>
		<td><?php 
		
		if ($metrics['hist_avg_response'] > 0)
			print number_format($metrics['hist_min_convo_time'], 1).' minutes'; 
		else
			print '-';
			
		?></td>
	</tr>
	<tr>
		<td>Longest Conversation</td>
		<td><?php 
		
		if ($metrics['hist_avg_response'] > 0)
			print number_format($metrics['hist_max_convo_time'], 1).' minutes'; 
		else
			print '-';
			
		?></td>
	</tr>
	<tr>
		<td>Average Response Time</td>
		<td><?php 
		
		if ($metrics['hist_avg_response'] > 0)
			print number_format($metrics['hist_avg_response'], 3).' seconds'; 
		else
			print '-';
			
		?></td>
	</tr>
	<tr>
		<td>Worst Response Time</td>
		<td><?php 
		
		if ($metrics['hist_max_response'] > 0)
			print number_format($metrics['hist_max_response'], 3).' seconds'; 
		else
			print '-';
			
		?></td>
	</tr>
	<tr>
		<td>Average Response IQ</td>
		<td><?php 
		
		if ($metrics['hist_iq_response'] > 0)
			print number_format($metrics['hist_iq_response'], 0); 
		else
			print '-';
			
		?></td>
	</tr>
	</table>
</div>

<!--
<div class="dashboard-widget">
<h2>Performance Trends</h2>


</div>


<div class="clear" style="height: 2em;"></div>
-->

<div class="dashboard-widget">
<h2>General Load</h2>

	<table class="dashboard-stats">
	<tr>
		<td>Average Clients</td>
		<td><?php print number_format(round($metrics['hist_avg_clients']), 0); ?></td>
	</tr>
	<tr>
		<td>Most Clients</td>
		<td><?php print number_format($metrics['hist_max_clients'], 0); ?></td>
	</tr>
	<tr>
		<td>Average Load</td>
		<td><?php print number_format($metrics['hist_load'], 2); ?> %</td>
	</tr>
	</table>
</div>

<!--
<div class="dashboard-widget">
<h2>Load Trends</h2>


</div>

-->


<script type="text/javascript">
/*
window.setTimeout(function() {
	location.reload();
}, 60000);
*/
</script>



<!-- WHAT BOT IS SAYING  ? -->

<!-- trends: average clients, average load, client interactions, iq, average response time, aveage convo duration -->
<!-- possible periods:  typical 24 hours, typical week, typical month, typical year
past 24-hours, past week, past month, past year
today, yesterday, this week, last week, this month, last month, this year -->
