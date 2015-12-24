<?php



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





