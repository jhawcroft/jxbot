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

