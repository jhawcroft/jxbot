<?php

require_once(dirname(__FILE__) . '/jxbot/core/common.php');


jxbot_start_session();


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


<form method="post" action="">

<div id="bot-output"><?php
if (isset($_REQUEST['input']))
	print Converse::get_response($_REQUEST['input']);
else
	print Converse::get_greeting();
?></div>

<p><input type="text" name="input" id="user-input" size="100"></p>

<p><input type="submit" id="user-submit" value="Say"></p>

</form>


</body>
</html>
