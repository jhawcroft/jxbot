<?php

require_once(dirname(__FILE__) . '/jxbot/core/jxbot.php');

JxBot::init_client();


// will need to be careful about sessions if this ends up being a plug-in on wordpress
// for example, will need the ability to use the wordpress session to store relevant data
// or some other means, and not start a session automatically


?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Bot</title>
</head>
<body>

<?php if (JxBotConverse::bot_available()) { ?>

<form method="post" action="">

<div id="bot-output"><?php
JxBotConverse::resume_conversation( JxBot::start_session() );
if (isset($_REQUEST['input']))
	print JxBotConverse::get_response($_REQUEST['input']);
else
	print JxBotConverse::get_greeting();
?></div>

<p><input type="text" name="input" id="user-input" size="100"></p>

<p><input type="submit" id="user-submit" value="Say"></p>

</form>

<?php } else { ?>

<h1>Bot Not Available</h1>

<p>Sorry, the chat bot is not currently available and is down for maintenance.</p>

<p>Please check back again later!</p>

<?php } ?>


</body>
</html>
