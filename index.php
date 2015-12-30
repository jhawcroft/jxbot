<?php
/*******************************************************************************
An example HTML interface to the JxBot programmers API
Customise this webpage or make your own.
*******************************************************************************/

/* include the JxBot client API */
require_once(dirname(__FILE__) . '/jxbot/core/client.php');

/* initalize the client API */
JxBotClient::init();

/* start/resume a conversation using a cookie */
JxBotClient::resume_conversation();


?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Bot</title>
</head>
<body>

<?php  /* check if the bot is actually online and not down for maintenance */
if (JxBotClient::is_available()) { 
?>

<form method="post" action="">


<!-- BOT OUTPUT: -->
<div id="bot-output"><?php

/* generate the bot response to the user-input;
or an appropriate greeting if the page has just loaded */
if (isset($_REQUEST['input']))
	print JxBotClient::respond($_REQUEST['input']);
else
	print JxBotClient::respond(null);
	
?></div>


<!-- USER INPUT: -->
<p><input type="text" name="input" id="user-input" size="100" autofocus></p>
<p><input type="submit" id="user-submit" value="Say"></p>


</form>

<?php 
} else {  /* the bot is offline for maintenance: */
?>


<!-- MAINTENANCE MESSAGE -->

<h1>Bot Not Available</h1>

<p>Sorry, the chat bot is not currently available and is down for maintenance.</p>

<p>Please check back again later!</p>



<?php } ?>

</body>
</html>
