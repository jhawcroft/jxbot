<?php


			

class JxBotInstaller
{

	private static function installer_head()
	{
?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>JxBot: Installer</title>
<link rel="base" href="<?php print JxBotConfig::bot_url(); ?>jxbot/">
<link href="https://fonts.googleapis.com/css?family=Roboto+Condensed|Oswald:700" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="<?php print JxBotConfig::bot_url(); ?>jxbot/core/css/admin.css">
<link rel="stylesheet" type="text/css" href="<?php print JxBotConfig::bot_url(); ?>jxbot/core/css/fancy.css">
<script type="text/javascript" src="<?php print JxBotConfig::bot_url(); ?>jxbot/core/js/admin.js"></script>
</head>
<body>
<div id="install-container-outer">
<div id="install-container">
<form method="post" action="">
<?php
	}


	private static function installer_foot()
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
	
	
	private static function page_welcome($in_msgs)
	{
?>

<img src="<?php print JxBotConfig::bot_url(); ?>jxbot/core/gfx/robot-small.png" id="welcome-bot">
<h1>Welcome to JxBot!</h1>

<p>To get started, please provide the details of the MySQL database you will use for this installation.</p>

<p>If you don't have a MySQL database, you will need to create one first.  Consult your hosting provider for assistance creating a MySQL database.</p>

<?php
if ($in_msgs != '') print '<p>'.$in_msgs.'</p>';
?>

<div class="field"><label for="db_host">Host: </label>
<input type="text" name="db_host" id="db_host" size="25" value="localhost"></div>

<div class="field"><label for="db_name"><strong>Database Name: </strong></label>
<input type="text" name="db_name" id="db_name" size="25" value="jxbot"></div>

<div class="field"><label for="db_prefix">Table Prefix: </label>
<input type="text" name="db_prefix" id="db_prefix" size="25" value="jxbot_"></div>

<div class="field"><label for="db_username"><strong>Username: </strong></label>
<input type="text" name="db_username" id="db_username" size="25" value=""></div>

<div class="field"><label for="db_password"><strong>Password: </strong></label>
<input type="text" name="db_password" id="db_password" size="25" value=""></div>

<p>Unless your hosting provider indicates otherwise, you probably only need to set the <em>database name</em>, <em>username</em> and <em>password</em>.</p>

<p class="right"><button type="submit" name="page" value="configure">Continue</button></p>

<?php
	}
	
	
	private static function page_configure()
	{
		$inputs = JxBotUtil::inputs('db_host,db_name,db_prefix,db_username,db_password');
			
JxWidget::hidden($inputs, 'db_host,db_name,db_prefix,db_username,db_password');
?>

<h1>Configure Bot System</h1>

<p>Before we run the installation, we need to find out a little about your bot.</p>

<div class="field"><label for="bot_name">Name: </label>
<input type="text" name="bot_name" id="bot_name" size="25" value="My Awesome Bot"></div>

<div class="field"><label for="admin_password">Administration Password: </label>
<input type="text" name="admin_password" id="admin_password" size="25" value=""></div>

<div class="field"><label for="bot_tz">Timezone: </label>
<?php JxBotConfig::widget_timezone(); ?></div>


<p class="right"><button type="submit" name="action" value="install">Install</button></p>

<?php
	}
	
	
	private static function get_configuration()
	{
		$inputs = JxBotUtil::inputs('db_host,db_name,db_prefix,db_username,db_password,bot_tz,bot_name,admin_password');
		$config = '';
		
		$config .= "<?php\n\n";
		$config .= '$jxbot[\'db_host\'] = "' . $inputs['db_host']."\";\n";
		$config .= '$jxbot[\'db_name\'] = "' . $inputs['db_name']."\";\n";
		$config .= '$jxbot[\'db_prefix\'] = "' . $inputs['db_prefix']."\";\n";
		$config .= '$jxbot[\'db_username\'] = "' . $inputs['db_username']."\";\n";
		$config .= '$jxbot[\'db_password\'] = "' . $inputs['db_password']."\";\n\n";
		$config .= '$jxbot[\'bot_url\'] = "' . JxBotConfig::option('bot_url') . "\";\n\n";
		
		return $config;
	}
	
	
	public static function try_write_config()
	{
		$config_file = dirname(dirname(__FILE__)) . '/config.php';
		
		if (file_exists($config_file)) return true;
	
		$fh = @fopen($config_file, 'w');
		if (!$fh) return false;
		if (fwrite($fh, JxBotInstaller::get_configuration()) === false) return false;
		fclose($fh);
	
		return true;
	}
	
	
	private static function do_install(&$out_msgs)
	{
		$inputs = JxBotUtil::inputs('db_host,db_name,db_prefix,db_username,db_password,bot_tz,bot_name,admin_password');
		
		// database configuration
		if (!JxBotInstaller::try_write_config()) return 'manual';
		if (!JxBotConfig::load_config()) return 'manual';
		
		// database connection
		if (!JxBotConfig::try_connect_db()) 
		{
			$out_msgs = 'Unable to connect to the database with the specified settings.  Please check the settings and try again.';
			return 'welcome';
		}
		
		// schema creation
		require_once(dirname(__FILE__).'/schema.php');
		
		// configure system
		JxBotConfig::set_option('bot_name', $inputs['bot_name']);
		JxBotConfig::set_option('bot_tz', $inputs['bot_tz']);
		JxBotConfig::set_option('admin_hash', hash('sha256', $inputs['admin_password']));
		JxBotConfig::save_configuration();
		
		return 'complete';
	}
	
	
	private static function page_error($in_msgs)
	{
?>
<h1>Installation Failed</h1>

<p>Sorry, but the installation failed:</p>

<p><?php print $in_msgs; ?></p>

<p><button type="submit" name="page" value="welcome">Try Again</button></p>

<?php
	}
	
	
	private static function page_complete()
	{
?>
<h1>Installation Complete!</h1>

<p>Installation was successful.</p>

<p>You can now <a href="<?php print JxBotConfig::bot_url(); ?>jxbot/">login to your bot's administration panel</a> to upload AIML and further configure your bot.</p>

<?php
	}
	
	
	private static function page_manual()
	{
		$inputs = JxBotUtil::inputs('db_host,db_name,db_prefix,db_username,db_password,bot_tz,bot_name,admin_password');
		
JxWidget::hidden($inputs, 'db_host,db_name,db_prefix,db_username,db_password,admin_password');
?>
<h1>Manual Configuration Required</h1>

<p>Sorry, JxBot does not have permission to write the configuration file.  You will need to create the file <strong>config.php</strong> in the <strong>jxbot</strong> folder of your installation.</p>

<p>Copy and paste the database configuration settings from this window your configuration file.</p>

<blockquote>
<?php print nl2br(htmlentities(JxBotInstaller::get_configuration())); ?>
</blockquote>

<p>When you're done, you can continue the installation.</p>
 

<p class="right"><button type="submit" name="action" value="install">Install</button></p>

<?php
	}


	public static function generate()
	{
		$inputs = JxBotUtil::inputs('page,action');
		$page = $inputs['page'];
		if ($page == '') $page = 'welcome';
		
		$msgs = '';
		if ($inputs['action'] == 'install') 
			$page = JxBotInstaller::do_install($msgs);

		JxBotInstaller::installer_head();
		
		if ($page == 'welcome') JxBotInstaller::page_welcome($msgs);
		else if ($page == 'configure') JxBotInstaller::page_configure();
		
		else if ($page == 'error') JxBotInstaller::page_error($msgs);
		else if ($page == 'manual') JxBotInstaller::page_manual();
		else if ($page == 'complete') JxBotInstaller::page_complete();
		
		JxBotInstaller::installer_foot();
	}
}


JxBotInstaller::generate();


