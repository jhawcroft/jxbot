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
 
require_once('defaults.php');
require_once('util.php');
require_once('db.php');

jxbot_start_session();


global $params;
$params = array();
$params['db_host'] = (isset($_REQUEST['db_host']) ? $_REQUEST['db_host'] : 'localhost');
$params['db_name'] = (isset($_REQUEST['db_name']) ? $_REQUEST['db_name'] : '');
$params['db_prefix'] = (isset($_REQUEST['db_prefix']) ? $_REQUEST['db_prefix'] : 'jxbot_');
$params['db_username'] = (isset($_REQUEST['db_username']) ? $_REQUEST['db_username'] : '');
$params['db_password'] = (isset($_REQUEST['db_password']) ? $_REQUEST['db_password'] : '');

$params['bot_name'] = (isset($_REQUEST['bot_name']) ? $_REQUEST['bot_name'] : '');
$params['bot_tz'] = (isset($_REQUEST['bot_tz']) ? $_REQUEST['bot_tz'] : '');



function install_head()
{
?>
<!DOCTYPE html>
<html>
<head>
<title>JxBot: Install</title>
<style>

<?php
include('styles.css');
?>


div#outer
{
	position: relative;
	width: 100%;
	height: 100%;
}


div#container
{
	margin: 0 auto;
	width: 30em;
	
	border-radius: 10px;
	
	position: relative;
	
	top: 45%;
	-webkit-transform: translateY(-50%);
	-ms-transform: translateY(-50%);
	transform: translateY(-50%);
	
	padding: 20px;
	background-color: white;
	box-shadow: 0px 2px 4px 0 black;
}


h1
{
	margin: 0;
	font-family: sans-serif;
	font-size: 18pt;
	font-weight: normal;
}




p#buttons
{
	margin-bottom: 0;
}

p.error
{
	padding: 4px 8px 4px 8px;
	background-color: #ffcccc;
	border-radius: 5px;
	cursor: pointer;
}

span.error
{
	font-weight: bold;
	font-family: sans-serif;
	text-transform: uppercase;
	
}

textarea
{
	width: 30em;
	height: 10em;
	resize: none;
}


</style>
</head>
<body>
<div id="outer">
<div id="container">
<form method="post" action="">
<?php
}


function install_foot()
{
?>

<div class="clear"></div>

</form>
</div>
</div>
</body>
</html>
<?php
}


function try_connect()
{
	global $params, $jxbot_config;
	
	$jxbot_config['db_host'] = $params['db_host'];
	$jxbot_config['db_name'] = $params['db_name'];
	$jxbot_config['db_prefix'] = $params['db_prefix'];
	$jxbot_config['db_username'] = $params['db_username'];
	$jxbot_config['db_password'] = $params['db_password'];
	
	return jxbot_connect_db();
}


function generate_config()
{
	global $params;
	
	$config = '';
	$config .= "<?php\n\n";
	$config .= '$jxbot_config[\'db_host\'] = "' . $params['db_host']."\";\n";
	$config .= '$jxbot_config[\'db_name\'] = "' . $params['db_name']."\";\n";
	$config .= '$jxbot_config[\'db_prefix\'] = "' . $params['db_prefix']."\";\n";
	$config .= '$jxbot_config[\'db_username\'] = "' . $params['db_username']."\";\n";
	$config .= '$jxbot_config[\'db_password\'] = "' . $params['db_password']."\";\n\n";
	
	$config .= '$jxbot_config[\'bot_url\'] = "' . JxBotUtil::request_url() . "\";\n";
	
	return $config;
}


function try_write_config()
{
	global $jxbot_config;
	
	$config_file = $jxbot_config['config_dir'] . 'config.php';
	
	//print $config_file;
	
	$fh = @fopen($config_file, 'w');
	if (!$fh) return false;
	if (fwrite($fh, generate_config()) === false) return false;
	fclose($fh);
	
	return true;
}


function print_config()
{
	global $params;
?>
<input name="stage" type="hidden" value="1">
<input name="already-tried" type="hidden" value="1">
<input type="hidden" name="db_host" value="<?php print $params['db_host']; ?>">
<input type="hidden" name="db_name" value="<?php print $params['db_name']; ?>">
<input type="hidden" name="db_prefix" value="<?php print $params['db_prefix']; ?>">
<input type="hidden" name="db_username" value="<?php print $params['db_username']; ?>">
<input type="hidden" name="db_password" value="<?php print $params['db_password']; ?>">


<h1>Manual Configuration Required</h1>

<p>Unfortunately JxBot doesn't seem to have permission to write the configuration file:</p>

<blockquote>jxbot/config.php</blockquote>

<p>Please create the file config.php in the jxbot directory on your server.  You can copy and paste the configuration as follows:</p>

<p><textarea id="configuration-data" rows="10" cols="40" readonly="true"><?php 
print generate_config();
?></textarea></p>

<p>When you've created the configuration file, you can continue with the installation.</p>

<?php if (isset($_POST['already-tried'])) { ?>
<p class="error" onclick="this.style.display = 'none';">
<span class="error">Error: </span>Please ensure you install the configuration file before continuing with the installation.
</p>
<?php } ?>

<p class="right" id="buttons"><input type="submit" value="Continue" class="blue"></p>

<?php
}


function print_done()
{
?>
<h1>Installation Completed</h1>

<p>Installation completed successfully!</p>

<p>You can now <a href="">login to your new bot</a>.</p>
<?php
}


function do_install()
{
	global $jxbot_db;
	require('schema.php');
}


function install_welcome($in_did_error)
{
	global $params;
?>

<input type="hidden" name="stage" value="1">

<h1>Welcome to JxBot!</h1>

<p>To begin the JxBot installation, please provide your MySql database details.</p>

<p>For assistance creating and configuring a database, speak to your hosting provider or <a href="">try these simple instructions</a> if you have a cPanel account.</p>

<?php if ($in_did_error) { ?>
<p class="error" onclick="this.style.display = 'none';">
<span class="error">Error: </span>Sorry, I was not able to establish a connection to the database with the details provided. Please check the details and try again.
</p>
<?php } ?>

<p class="field"><label for="db_host">Host: </label>
<input type="text" name="db_host" id="db_host" size="25" value="<?php print $params['db_host']; ?>"></p>

<p class="field"><label for="db_host">Database Name: </label>
<input type="text" name="db_name" id="db_name" size="25" value="<?php print $params['db_name']; ?>"></p>

<p class="field"><label for="db_host">Table Prefix: </label>
<input type="text" name="db_prefix" id="db_prefix" size="25" value="<?php print $params['db_prefix']; ?>"></p>

<p class="field"><label for="db_host">Username: </label>
<input type="text" name="db_username" id="db_username" size="25" value="<?php print $params['db_username']; ?>"></p>

<p class="field"><label for="db_host">Password: </label>
<input type="text" name="db_password" id="db_password" size="25" value="<?php print $params['db_password']; ?>"></p>


<p class="right" id="buttons"><input type="submit" value="Continue" class="blue"></p>

<?php
}


function install_setup()
{
	global $params;
?>

<input type="hidden" name="stage" value="2">

<h1>Configure Robot</h1>

<p>Database configuration was successful.</p>

<p>Please provide a few details so your chat bot can be configured.</p>

<p class="field"><label for="bot_password">Admin Password: </label>
<input type="text" name="bot_password" id="bot_password" size="20"></p>

<p class="field"><label for="bot_name">Bot Name: </label>
<input type="text" name="bot_name" id="bot_name" size="40" value="<?php print $params['bot_name']; ?>"></p>

<p class="field"><label for="bot_tz">Timezone: </label>
<select name="bot_tz" id="bot_tz" class="focusable">
<option value=""></option>
<?php
$timezone_identifiers = DateTimeZone::listIdentifiers();
foreach ($timezone_identifiers as $tz)
{
	print '<option value="'.$tz.'">'.$tz.'</option>';
}
?>
</select></p>



<p class="right" id="buttons"><input type="submit" value="Continue" class="blue"></p>


<?php
}


function do_configure()
{

}


install_head();


if (!isset($_POST['stage'])) 
{
	if (isset($jxbot_db)) install_setup();
	else install_welcome(false);
}

else if ($_POST['stage'] == 1)
{
	if (!try_connect()) install_welcome(true);
	else if (!try_write_config()) print_config();
	else install_setup();
}
	
else if ($_POST['stage'] == 2)
{
	do_install();
	do_configure();
	print_done();
}
		

install_foot();

