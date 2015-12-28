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

/* the bot administration pages */

if (!defined('JXBOT')) die('Direct script access not permitted.');




class JxBotAdmin
{

	private static $page = null;
	
	private static $all_pages = array(
		array('dashboard', 'Dashboard'),
		array('chat', 'Talk to the Bot'),
		array('logs', 'Logs'),
		array('client', 'Client Defaults'),
		array('database', 'Database'),
		/*array('sets', 'Sets'), // should consider putting these as tabs under Database to minimise admin clutter*/
		/*array('subs', 'Substitutions'),*/
		array('import', 'Import'),
		array('bot', 'Bot Personality'),
		array('settings', 'System Settings'),
		/*array('export', 'Export / Backup'),*/
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
		session_destroy();
		
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
	
	
	private static function ensure_logged_in()
	{
		/* is the client logged in? */
		if ( (!isset($_SESSION['jxbot-admin'])) || 
			($_SESSION['jxbot-admin'] !== 1) ||
			(!isset($_SESSION['jxbot-last'])) )
		{
			require(dirname(__FILE__).'/login.php');
			exit;
		}
	
		/* has the session expired? */
		$admin_timeout = intval(JxBotConfig::option('admin_timeout')) * 60;
		if (($admin_timeout != 0) && ((time() - $_SESSION['jxbot-last']) > $admin_timeout))
		{
			unset($_SESSION['jxbot-admin']);
			session_destroy();
			
			require(dirname(__FILE__).'/login.php');
			exit;
		}
	
		/* continue as logged in */
		define('JXBOT_ADMIN', 1);
		$_SESSION['jxbot-last'] = time();
	}
	
	
public static function admin_generate()
{
	JxBotAdmin::ensure_logged_in();
	

	JxBotAdmin::determine_page();
	
	
	if (JxBotAdmin::$page[0] == 'logout')
		JxBotAdmin::do_logout();
	

?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>JxBot: Administration</title>
<link rel="base" href="<?php print JxBotConfig::bot_url(); ?>jxbot/">
<!link href="https://fonts.googleapis.com/css?family=Roboto+Condensed|Oswald:700" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="<?php print JxBotConfig::bot_url(); ?>jxbot/core/css/admin.css">
<link rel="stylesheet" type="text/css" href="<?php print JxBotConfig::bot_url(); ?>jxbot/core/css/bubble.css">
<link rel="stylesheet" type="text/css" href="<?php print JxBotConfig::bot_url(); ?>jxbot/core/css/fancy.css">
<link rel="stylesheet" type="text/css" href="<?php print JxBotConfig::bot_url(); ?>jxbot/core/css/phpinfo.css">
<script type="text/javascript" src="<?php print JxBotConfig::bot_url(); ?>jxbot/core/js/admin.js"></script>


</head>
<body>

<div id="nav">
<div id="nav-top">
	<img src="<?php print JxBotConfig::bot_url(); ?>jxbot/core/gfx/robot-tiny.png" style="float:right;"><h1>JxBot</h1>
</div>

<?php JxBotAdmin::admin_sidebar(); ?>


<div id="nav-bot"></div>
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
		print '<li><a href="?page='.$page_def[0].'"'.
			($page_def[0] === JxBotAdmin::$page[0] ? ' class="current"' : '').
			'>'.$page_def[1].'</a></li>';
	}
?>
</ul>

<?php
}


public static function admin_page()
{
?>
<h1><?php print JxBotAdmin::$page[1]; ?></h1>
<form method="post" action="" name="admin-form" id="admin-form" enctype="multipart/form-data">
<?php JxWidget::$form_id = 'admin-form'; ?>

<?php 
require_once(dirname(__FILE__).'/admin_'.JxBotAdmin::$page[0].'.php');
?>

</form>
<?php
}


	public static function check_and_login()
	/* checks user credentials and logs them in if they're valid */
	{
		$inputs = JxBotUtil::inputs('username,password');
		
		/* check the user hasn't logged in too often recently */
		$stmt = JxBotDB::$db->prepare('SELECT COUNT(*) FROM login
			WHERE stamp > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
				AND username=?');
		$stmt->execute(array($inputs['username']));
		$recent_logins = intval( $stmt->fetchAll(PDO::FETCH_NUM)[0][0] );
		
		if ($recent_logins > 5) 
		{
			return false;
		}
		
		/* are credentials wrong? */
		if ((JxBotConfig::option('admin_user') != $inputs['username']) ||
			(JxBotConfig::option('admin_hash') != hash('sha256', $inputs['password'])))
		{
			$stmt = JxBotDB::$db->prepare('INSERT INTO login
				(username, note) VALUES (?, ?)');
			$stmt->execute(array($inputs['username'], 'failure'));
		
			return false;
		}
			
		/* do the login */
		$_SESSION['jxbot-admin'] = 1;
		$stmt = JxBotDB::$db->prepare('INSERT INTO login
			(username, note) VALUES (?, ?)');
		$stmt->execute(array($inputs['username'], 'success'));
		$_SESSION['jxbot-last'] = time();
		
		/* generate the admin page */
		JxBotAdmin::admin_generate();
		
		return true;
	}

}




