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
		print '<div class="widget-toggle focusable toggle-switch" tabindex="0" data-name="'.$in_name.'">';
		print '<div class="'.($in_state ? 'yes' : 'off').'">Yes</div>';
		print '<div class="'.(!$in_state ? 'no' : 'off').'">No</div>';
		print '</div>';
		print '<input type="checkbox" name="'.$in_name.'" class="widget-toggle">';
		print '<div class="clear"></div>';
	}
	
	
	// have a tiny width class for numbers et al.
	// a medium width class for dates & times
	// a normal width class for short text strings such as names and a few words
	// a long width class for longer strings, like URLs, etc.
	// and a huge width class for full-width sentences/paragraphs
	
	public static function textfield($in_name, $in_label, $in_value, $in_max_length)
	{
		print '<p class="field"><label for="'.$in_name.'">'.$in_label.': </label>';
		print '<input type="text" name="'.$in_name.'" id="'.$in_name.'" size="40" value="';
		print $in_value.'">';
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
		print '<table>';
		print '<tr>';
		foreach ($in_column_defs as $col)
		{
			print '<th>';
			print $col['label'];
			print '</th>';
		}
		print '</tr>';
		
		$col_count = count($in_column_defs);
		foreach ($in_data_rows as $row)
		{
			print '<tr>';
			for ($c = 0; $c < $col_count; $c++)
			{
				print '<td>'.$row[$c].'</td>';
			}
			print '</tr>';
		}
		
		print '</table>';
	}
	
}

