<?php
/*
Plugin Name: JxBot
Plugin URI: http://joshhawcroft.org/jxbot/wordpress-plugin/
Description: An easy widget front-end to a JxBot chat robot.
Version: 0.91
Author: Joshua Hawcroft
Author URI: http://joshhawcroft.org/
License: MIT
*/


class JxBotWPWidget extends WP_Widget
{

	function __construct()
	// initalization of the widget
	{
		parent::WP_Widget(
			'jxbot', 
			__('JxBot', 'JxBotWPWidget'),
			array('description'=> __('Embed a chat interface to a JxBot.', 'JxBotWPWidget'))
			);
	}
	
	
	function form($in_instance)
	// administrative back-end widget configuration
	{
		if ($in_instance)
		{
			$title = esc_attr($in_instance['title']);
			$bot_url = esc_attr($in_instance['bot_url']);
		}
		else
		{
			$title = '';
			$bot_url = '';
		}
		
?>
<p>
<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', 'JxBotWPWidget'); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
</p>
 
<p>
<label for="<?php echo $this->get_field_id('bot_url'); ?>"><?php _e('Bot URL:', 'JxBotWPWidget'); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('bot_url'); ?>" name="<?php echo $this->get_field_name('bot_url'); ?>" type="text" value="<?php echo $bot_url; ?>" />
</p>
 

<?php
	}
	
	
	function update($in_new_instance, $in_old_instance)
	// saving administrative configuration changes
	{
		$instance = $in_old_instance;
		$instance['title'] = strip_tags($in_new_instance['title']);
		$instance['bot_url'] = strip_tags($in_new_instance['bot_url']);
		return $instance;
	}
	
	
	function widget($in_args, $in_instance)
	// displaying the widget on the site front-end
	{
		extract($in_args);
		$title = apply_filters('widget_title', $in_instance['title']);
		$bot_url = $in_instance['bot_url'];
		print $before_widget;
		print '<div class="jxbot-widget">';
		
		if ($title)
			print $before_title . $title . $after_title;
		
?>

<script type="text/javascript">
var jxbot_url = <?php print json_encode($bot_url,  JSON_UNESCAPED_SLASHES); ?>;
</script>

<input type="text" onkeypress="return jxbot_input_keypress(event);" class="jxbot-widget-input" placeholder="Talk" style="padding: 10px; border: 0; background:-webkit-gradient(linear, 0 0, 0 100%, from(#f04349), to(#c81e2b));
  background:-moz-linear-gradient(#f04349, #c81e2b);
  background:-o-linear-gradient(#f04349, #c81e2b);
  background:linear-gradient(#f04349, #c81e2b);
  -webkit-border-radius:10px;
  -moz-border-radius:10px;
  border-radius:10px;
  color: white;">

<div class="jxbot-widget-output" style="display: none; padding: 10px; border: 0; 
 background:-webkit-gradient(linear, 0 0, 0 100%, from(#2e88c4), to(#075698));
  background:-moz-linear-gradient(#2e88c4, #075698);
  background:-o-linear-gradient(#2e88c4, #075698);
  background:linear-gradient(#2e88c4, #075698);
  -webkit-border-radius:10px;
  -moz-border-radius:10px;
  border-radius:10px;
  color: white;">Here is the response</div>
  
  <div class="jxbot-widget-clear"></div>

<?php
		
		print '</div>';
		print $after_widget;
	}
	
	
	public static function enqueue_scripts()
	{
		wp_enqueue_script( '', plugins_url(null, __FILE__).'/jxbot.js' );
		wp_enqueue_style( '', plugins_url(null, __FILE__).'/jxbot.css' );
	}
	
	
	public static function init_plugin()
	{
		add_action('widgets_init', create_function('', 'return register_widget("JxBotWPWidget");'));
		
		add_action('wp_enqueue_scripts', array('JxBotWPWidget', 'enqueue_scripts'));
	}
}


JxBotWPWidget::init_plugin();



