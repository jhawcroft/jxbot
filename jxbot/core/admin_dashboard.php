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
	
	$stmt = JxBotDB::$db->prepare('SELECT COUNT(*) FROM word');
	$stmt->execute();
	$metrics['dictionary_size'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	$stmt = JxBotDB::$db->prepare('SELECT COUNT(*) FROM category');
	$stmt->execute();
	$metrics['category_count'] = $stmt->fetchAll(PDO::FETCH_NUM)[0][0];
	
	$stmt = JxBotDB::$db->prepare('SELECT COUNT(*) FROM sequence');
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
<tr>
	<td>Dictionary Size</td>
	<td><?php print $metrics['dictionary_size']; ?></td>
</tr>

</table>

