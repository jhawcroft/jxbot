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

if (!defined('JXBOT')) die('Direct script access not permitted.');


$error = false;
if (isset($_POST['username']) || isset($_POST['password']))
{
	$logged_in = JxBotAdmin::check_and_login();
	if (!$logged_in) $error = true;
}


?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>JxBot: Administration</title>
<link rel="base" href="<?php print JxBotConfig::bot_url(); ?>jxbot/">
<!--link href="https://fonts.googleapis.com/css?family=Roboto+Condensed|Oswald:700" rel="stylesheet" type="text/css"-->
<link href="<?php print JxBotConfig::bot_url(); ?>jxbot/core/css/fonts.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="<?php print JxBotConfig::bot_url(); ?>jxbot/core/css/admin.css">
<link rel="stylesheet" type="text/css" href="<?php print JxBotConfig::bot_url(); ?>jxbot/core/css/fancy.css">
</head>
<body>
<div id="login-container-outer">
<div id="login-container">
<form method="post" action="">

<h1>JxBot Administration</h1>

<?php if ($error) { ?>
<p class="error">Sorry, the username or password you entered is not correct.<p>
<?php } ?>

<p><label for="username">Username:</label>
<input type="text" name="username" id="username" size="20"></p>

<p><label for="password">Password:</label>
<input type="password" name="password" id="password" size="20"></p>

<p class="left"><small><a href="?forgot-password=1">Forgot my password</a></p>

<p class="right"><input type="submit" value="Login"></p>


<div class="clear"></div>
</form>
</div>
</div>
</body>
</html>

