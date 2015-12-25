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
 

class JxWidget
{

    const BUTTON_SUBMIT = 1;
    
    
    public static $form_id = '';
	

	// replace with radio button array & re-do styles
	public static function toggle_switch($in_name, $in_state)
	{
		//print '<div class="widget-toggle focusable toggle-switch" tabindex="0" data-name="'.$in_name.'">';
		//print '<div class="'.($in_state ? 'yes' : 'off').'">Yes</div>';
		//print '<div class="'.(!$in_state ? 'no' : 'off').'">No</div>';
		print '<fieldset class="fancy-switch" tabindex="0">';
		print '<input type="radio" name="'.$in_name.'" id="'.$in_name.'_1" value="1" '.
			($in_state == 1 ? ' checked="true"' : '').'>';
		print '<label for="'.$in_name.'_1" class="fancy-yes">Yes</label>';
		print '<input type="radio" name="'.$in_name.'" id="'.$in_name.'_0" value="0" '.
			($in_state == 0 ? ' checked="true"' : '').'>';
		print '<label for="'.$in_name.'_0" class="fancy-no">No</label>';
		print '</fieldset>';
		//print '</div>';
		//print '<div class="clear"></div>';
	}
	
	
	// have a tiny width class for numbers et al.
	// a medium width class for dates & times
	// a normal width class for short text strings such as names and a few words
	// a long width class for longer strings, like URLs, etc.
	// and a huge width class for full-width sentences/paragraphs
	
	public static function textfield($def) //$in_name, $in_label, $in_value, $in_max_length, $in_auto = false)
	{
		print '<p class="field"><label for="'.$def['name'].'">'.$def['label'].': </label>';
		print '<input type="text" name="'.$def['name'].'" id="'.$def['name'].'" size="40"';
		print ' value="'. (isset($def['value']) ? $def['value'] : '') . '"';
		if (isset($def['autofocus']) && $def['autofocus'] === true) print ' autofocus';
		print '>';
		print '</p>';
	}
	
	// memo field could allow control over desired row count visible, scrolling, etc.
	public static function memofield($in_name, $in_label, $in_value, $in_rows_visible, $in_scrolls)
	{
		print '<p class="field"><label for="'.$in_name.'">'.$in_label.': </label>';
		print '<textarea name="'.$in_name.'" id="'.$in_name.'" rows="'.$in_rows_visible.'" cols="80">';
		print $in_value.'</textarea>';
		print '</p>';
	}
	
	
	public static function button($in_label, $in_name = NULL, $in_value = NULL)
	{
		print '<button';
		if ($in_name) print ' name="'.$in_name.'"';
		if ($in_value) print ' value="'.$in_value.'"';
		print '>';
		print $in_label.'</button>';
	}
	
	/*public static function button($in_label, $in_hint = 1, $in_action = NULL)
	{
		print '<button';
		if (substr($in_action, 0, 5) == 'POST:')
		{
			print ' form="'.JxWidget::$form_id.'"';
			print ' formmethod="POST"';
			print ' formaction="'.substr($in_action,5).'"';
			$in_hint |= JxWidget::BUTTON_SUBMIT;
		}
		else if (substr($in_action, 0, 4) == 'GET:')
		{
			print ' form="'.JxWidget::$form_id.'"';
			print ' formmethod="GET"';
			print ' formaction="'.substr($in_action,4).'"';
			$in_hint |= JxWidget::BUTTON_SUBMIT;
		}
		else
		{
			print ' onclick="'.$in_action.'"';
		}
		print ($in_hint & JxWidget::BUTTON_SUBMIT ? ' type="submit"' : '');
		print '>'.$in_label.'</button>';
	}*/
	
	
	public static function grid($in_column_defs, &$in_data_rows)
	{
		$key_indexes = array();

		print '<table>';
		print '<tr>';

		$index = -1;
		foreach ($in_column_defs as $col)
		{
			$index++;
			if (isset($col['key']) && $col['key'] === true) $key_indexes[] = $col['id'];
			if (isset($col['visible']) && $col['visible'] === false) continue;
			print '<th>';
			print $col['label'];
			print '</th>';
		}
		print '</tr>';
		
		$col_count = count($in_column_defs);
		foreach ($in_data_rows as $row)
		{
			print '<tr>';
			foreach ($in_column_defs as $col)
			{
				if (isset($col['visible']) && $col['visible'] === false) continue;
				
				$is_link = isset($col['link']);
				
				if ($col['id'] === ':delete') 
					$value = '<img src="'.JxBotConfig::bot_url().'jxbot/core/gfx/delete16.png" alt="Delete">';
				else $value = $row[ $col['id'] ];
				
				print '<td';
				if ($col['id'] == ':delete') print ' style="width: 3em;"';
				print '>';
				if ($is_link) 
				{
					$key_value = $row[$key_indexes[0]];
					print '<a href="'.str_replace('$$', $key_value, $col['link']).'">';
				}
				
				if (isset($col['encode']) && $col['encode']) $value = htmlentities($value);
				print $value;
				
				if ($is_link) print '</a>';
				print '</td>';
			}
			print '</tr>';
		}
		
		print '</table>';
	}
	
	
	public static function small_delete_icon()
	{
		print '<img src="'.JxBotConfig::bot_url().'jxbot/core/gfx/delete16.png" alt="Delete">';
	}
	
	
	public static function hidden($in_data, $in_values)
	{
		if (is_array($in_data))
		{
			if (is_string($in_values)) $in_values = explode(',', $in_values);
			foreach ($in_values as $value_name)
			{
				print '<input type="hidden" name="'.$value_name.'" value="'.$in_data[$value_name].'">';
			}
		}
		else
		{
			print '<input type="hidden" name="'.$in_data.'" value="'.$in_values.'">';
		}
	}
}

