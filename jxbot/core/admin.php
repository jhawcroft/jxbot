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
 

class JxBotAdmin
{

	private static $page = null;
	
	private static $all_pages = array(
		array('dashboard', 'Dashboard'),
		/*array('talk', 'Talk'),*/
		/*array('personality', 'Personality'),*/
		array('database', 'Database'),
		/*array('import-export', 'Import/Export'),*/
		array('settings', 'Settings'),
		array('logs', 'Logs'),
		array('about', 'About JxBot'),
		array('logout', 'Logout')
	);
	
	
	private static function is_logged_in()
	{
		return (isset($_SESSION['jxbot-user']) && isset($_SESSION['jxbot-admin'])
			&& ($_SESSION['jxbot-admin'] === true));
	}
	
	
	private static function do_logout()
	{
		unset($_SESSION['jxbot-admin']);
		JxBotAdmin::$page = NULL;
		jxbot_finish_session();
		
		header("Location: ../\n");
		exit;
	}
	
	
	private static function determine_page()
	{
		if (!isset($_REQUEST['page'])) 
			JxBotAdmin::$page = JxBotAdmin::$all_pages[0];
		else 
		{
			$page_id = preg_replace('/[^a-z\-]+/i', '', $_REQUEST['page']);
			foreach (JxBotAdmin::$all_pages as $page_def)
			{
				if ($page_def[0] == $page_id)
				{
					JxBotAdmin::$page = $page_def;
					return;
				}
			}
			JxBotAdmin::$page = JxBotAdmin::$all_pages[0];
		}
	}
	
	
	
public static function admin_generate()
{
	JxBotAdmin::determine_page();
	
	if (JxBotAdmin::$page[0] == 'logout')
		JxBotAdmin::do_logout();
	

?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>JxBot: Administration</title>
<link rel="base" href="<?php print BotDefaults::bot_url(); ?>">
<link rel="stylesheet" type="text/css" href="<?php print BotDefaults::bot_url(); ?>jxbot/core/styles.css">
<link rel="stylesheet" type="text/css" href="<?php print BotDefaults::bot_url(); ?>jxbot/core/phpinfo.css">
<script type="text/javascript" src="<?php print BotDefaults::bot_url(); ?>jxbot/core/js/admin.js"></script>
<style>


div#nav
{
	float: left;
	width: 12em;
	background-color: #333;
	color: white;
	height: 100%;
}

div#nav-top
{
	margin: 1em;
	margin-bottom: 2em;
	margin-top: 1em;
}

div#nav-top h1
{
	margin: 0;
	font-family: sans-serif;
	font-size: 24pt;
	font-weight: bold;
}

div#page
{
	margin-left: 12em;
	height: 100%;
	overflow-y: auto;
	overflow-x: auto;
}

div#container
{
	padding: 2em;
	padding-top: 1em;
	
}

div#container h1
{
	font-family: sans-serif;
	font-size: 24pt;
	font-weight: normal;
	margin: 0;
	margin-bottom: 1em;
}


/*
Admin Navigation
*/

div#nav ul
{
	list-style: none;
	margin: 0;
	padding: 0;
	font-size: 12pt;
	font-family: sans-serif;
}

div#nav ul li
{
	display: block;
	margin: 0;
	padding: 0;
}

div#nav a
{
	display: block;
	color: white;
	text-decoration: none;
	padding: 10px 1em 10px 1em;
	border-bottom: 1px solid black;
}

div#nav li:last-child a
{
	border-bottom: 0;
}

div#nav a:hover
{
	background-color: rgb(20,20,20);
	color: rgb(102,153,255);
}

div#nav a.current
{
	background-color: rgb(0,102,204);
	color: rgb(255,255,255);
}



div#right-nav
{
	width: 30%;
}

div#right-nav h2
{
	margin-top: 0;
}

div#centre-content
{
	width: 67%;
}

div#centre-content h2
{
	margin-top: 0;
}


</style>
</head>
<body>

<div id="nav">
<div id="nav-top">
	<h1>JxBot</h1>
</div>

<?php JxBotAdmin::admin_sidebar(); ?>
</div>

<div id="page"><div id="container">
<?php JxBotAdmin::admin_page(); ?>
</div></div>

</body>
</html>
<?php
}


public static function admin_sidebar()
{
?>
<ul>
<?php	
	foreach (JxBotAdmin::$all_pages as $page_def)
	{
		print '<li><a href="?page='.$page_def[0].'">'.$page_def[1].'</a></li>';
	}
?>
</ul>

<?php
}


public static function admin_page()
{
?>
<h1><?php print JxBotAdmin::$page[1]; ?></h1>
<form method="post" action="" name="admin-form" id="admin-form">
<?php JxWidget::$form_id = 'admin-form'; ?>

<?php 
require_once(dirname(__FILE__).'/admin_'.JxBotAdmin::$page[0].'.php');
?>

</form>
<?php
}


}


JxBotAdmin::admin_generate();



