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
 

class JxBotAiml
{
	private static $state;
	
	const STATE_TOP = 0;
	const STATE_CAT = 1;
	const STATE_PAT = 2;
	const STATE_TMP = 3;
	const STATE_THT = 4;
	const STATE_TPC = 5;
	
	private static $category_id;
	private static $top_topic;
	private static $pattern;
	private static $template;
	private static $that;
	private static $topic;
	

	public static function _element_start($in_parser, $in_name, $in_attrs)
	{
		//print '< '.$in_name.'<br>';
		switch (JxBotAiml::$state)
		{
		case JxBotAiml::STATE_TOP:
			if ($in_name == 'category')
			{
				JxBotAiml::$state = JxBotAiml::STATE_CAT;
				JxBotAiml::$category_id = JxBotEngine::category_new('*', JxBotAiml::$top_topic);
				JxBotAiml::$that = '*';
				JxBotAiml::$topic = JxBotAiml::$top_topic;
			}
			else if ($in_name == 'topic' && isset($in_attrs['name']))
				JxBotAiml::$top_topic = $in_attribs['name'];
			break;
		case JxBotAiml::STATE_CAT:
			if ($in_name == 'pattern')
			{
				JxBotAiml::$state = JxBotAiml::STATE_PAT;
				JxBotAiml::$pattern = '';
			}
			elseif ($in_name == 'template')
			{
				JxBotAiml::$state = JxBotAiml::STATE_TMP;
				JxBotAiml::$template = '';
			}
			elseif ($in_name == 'that')
			{
				JxBotAiml::$state = JxBotAiml::STATE_THT;
				JxBotAiml::$that = '';
			}
			elseif ($in_name == 'topic')
			{
				JxBotAiml::$state = JxBotAiml::STATE_TPC;
				JxBotAiml::$topic = '';
			}
			break;
		}
	}
	
	
	public static function _element_end($in_parser, $in_name)
	{
		//print '/ '.$in_name.'<br>';
		switch (JxBotAiml::$state)
		{
		case JxBotAiml::STATE_TOP:
			if ($in_name == 'topic')
				JxBotAiml::$top_topic = '*';
			break;
		case JxBotAiml::STATE_CAT:
			if ($in_name == 'category')
				JxBotAiml::$state = JxBotAiml::STATE_TOP;
			break;
		case JxBotAiml::STATE_PAT:
			if ($in_name == 'pattern')
			{
				JxBotAiml::$state = JxBotAiml::STATE_CAT;
				JxBotEngine::pattern_add(JxBotAiml::$category_id, JxBotAiml::$pattern,
					'*', JxBotAiml::$top_topic);
			}
			break;
		case JxBotAiml::STATE_TMP:
			if ($in_name == 'template')
			{
				JxBotAiml::$state = JxBotAiml::STATE_CAT;
				JxBotEngine::template_add(JxBotAiml::$category_id, JxBotAiml::$template);
			}
			break;
		case JxBotAiml::STATE_THT:
			if ($in_name == 'that')
			{
				JxBotEngine::category_update(JxBotAiml::$category_id, 
					JxBotAiml::$that,
					JxBotAiml::$topic);
			}
			break;
		case JxBotAiml::STATE_TPC:
			if ($in_name == 'topic')
			{
				JxBotEngine::category_update(JxBotAiml::$category_id, 
					JxBotAiml::$that,
					JxBotAiml::$topic);
			}
			break;
		}
	}
	
	
	public static function _element_data($in_parser, $in_data)
	{
		//print '=>'.$in_data.'<br>';
		switch (JxBotAiml::$state)
		{
		case JxBotAiml::STATE_PAT:
			JxBotAiml::$pattern .= $in_data;
			break;
		case JxBotAiml::STATE_TMP:
			JxBotAiml::$template .= $in_data;
			break;
		}
	}
	
	
	public static function import($in_filename)
	{
		set_time_limit(300); // 5-minutes
		
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
		
		xml_set_element_handler($parser, 
			array('JxBotAiml', '_element_start'), 
			array('JxBotAiml', '_element_end'));
		xml_set_character_data_handler($parser, 
			array('JxBotAiml', '_element_data'));
			
		JxBotAiml::$state = JxBotAiml::STATE_TOP;
		JxBotAiml::$top_topic = '*';
		
		$fh = fopen($in_filename, 'r');
		if (!$fh) return "Server upload configuration error.  Couldn't open temporary file.";
		
		while ($data = fread($fh, 4096))
		{
			if (! xml_parse($parser, $data, feof($fh)) )
				return 'AIML error: Line ' . xml_get_current_line_number($parser) . ': ' . 
						xml_error_string(xml_get_error_code($parser));
		}
		
		fclose($fh);
		xml_parser_free($parser);
		
		return true;
	}
	
}

