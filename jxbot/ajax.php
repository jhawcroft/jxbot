<?php

require_once(dirname(__FILE__) . '/core/client.php');

JxBotClient::init();
JxBotClient::resume_conversation();

$output = '';
if (!JxBotClient::is_available())
	$output = 'Sorry, the bot is down for maintenance.  Please try again later.';
else
	$output = JxBotClient::respond(urldecode($_SERVER['QUERY_STRING']));

header("Content-type: text/plain; charset=utf-8");
print $output;