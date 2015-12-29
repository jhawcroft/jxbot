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

/* AIML parsing and generating capabilities for JxBot */

if (!defined('JXBOT')) die('Direct script access not permitted.');


// ! TODO - these classes need to be incorporated into one
// which we have an instance of, in all probability 

// THIS FILE IS A HORRIBLE MESS


class JxBotAimlPattern
{
	private $xml_parser;
	private $error;
	private $state;
	
	private $pattern;
	private $unnested_info;
	
	const STATE_PAT_ENCODE = 6;
	const STATE_PAT_TXT = 7;
	const STATE_PAT_BOT = 8;
	const STATE_PAT_SET = 9;
	
	
	private function set_error($in_error)
	{
		if ($this->error === '')
		{
			$this->error = $in_error;
			$this->$error_line = xml_get_current_line_number($this->xml_parser);
		}
	}
	

	public function _element_start($in_parser, $in_name, $in_attrs)
	{
		//print '< '.$in_name.'<br>';
		switch ($this->state)
		{	
		case JxBotAimlPattern::STATE_PAT_ENCODE:
			if ($in_name == 'pattern')
			{
				$this->state = JxBotAimlPattern::STATE_PAT_TXT;
				$this->pattern = '';
			}
			else $this->set_error('Illegal tag in pattern: '.$in_name);
			break;
		
		case JxBotAimlPattern::STATE_PAT_TXT:
			if ($in_name == 'bot')
			{
				$this->state = JxBotAimlPattern::STATE_PAT_BOT;
				$this->unnested_info = '';
				if (array_key_exists('name', $in_attrs))
					JxBotAiml::$unnested_info = ':'.$in_attrs['name'];
			}
			else if ($in_name == 'set')
			{
				$this->state = JxBotAimlPattern::STATE_PAT_SET;
				$this->unnested_info = '';
				if (array_key_exists('name', $in_attrs))
					$this->unnested_info = $in_attrs['name'].':';
			}
			else $this->set_error('Illegal tag in pattern: '.$in_name);
			break;
		case JxBotAimlPattern::STATE_PAT_BOT:
			$this->set_error('Illegal tag in pattern: '.$in_name);
			break;
		case JxBotAimlPattern::STATE_PAT_SET:
			$this->set_error('Illegal tag in pattern: '.$in_name);
			break;
		}
	}
	
	
	public function _element_end($in_parser, $in_name)
	{
		//print '/ '.$in_name.'  '.JxBotAiml::$state.'<br>';
		switch ($this->state)
		{	
		case JxBotAimlPattern::STATE_PAT_TXT:
			if ($in_name != 'pattern')
				$this->set_error('Illegal tag in pattern: '.$in_name);
			break;
		case JxBotAimlPattern::STATE_PAT_BOT:
			$this->state = JxBotAimlPattern::STATE_PAT_TXT;
			$this->pattern .= $this->unnested_info;
			break;
		case JxBotAimlPattern::STATE_PAT_SET:
			$this->state = JxBotAimlPattern::STATE_PAT_TXT;
			$this->pattern .= $this->unnested_info;
			break;
		}
	}
	
	
	public function _element_data($in_parser, $in_data)
	{
		//print '=>'.$in_data.'<br>';
		switch ($this->state)
		{
		case JxBotAimlPattern::STATE_PAT_TXT:
			$this->pattern .= $in_data;
			break;
		case JxBotAimlPattern::STATE_PAT_BOT:
			$this->unnested_info .= $in_data;
			break;
		case JxBotAimlPattern::STATE_PAT_SET:
			$this->unnested_info .= $in_data;
			break;
		}
	}
	
	
	public function __construct()
	{
		$this->xml_parser = xml_parser_create();
		xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($this->xml_parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
		
		xml_set_object($this->xml_parser, $this);
		
		xml_set_element_handler($this->xml_parser, '_element_start', '_element_end');
		xml_set_character_data_handler($this->xml_parser, '_element_data');
		
		$this->pattern = '';
		$this->state = JxBotAimlPattern::STATE_PAT_ENCODE;
		$this->error = '';
	}
	
	
	public function __destruct()
	{
		if ($this->xml_parser) xml_parser_free($this->xml_parser);
		$this->xml_parser = null;
	}
	
	
	public function translate($in_pattern)
	{
		$in_pattern = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><pattern>" . $in_pattern . '</pattern>';
		xml_parse($this->xml_parser, $in_pattern, true);
		return $this->pattern;
	}
}



class JxBotAimlImport
{
	private $xml_parser;
	private $_error;
	private $notices;
	private $state;
	
	private $aiml_version;
	private $aiml_topic;
	private $cat_topic;
	private $cat_that;
	private $cat_patterns;
	private $cat_templates;
	private $content;
	
	private $unrecognised;
	private $has_aiml1_learn;
	private $has_aiml1_gossip;
	private $has_aiml2_learn;
	private $has_aiml2_sraix;
	private $has_aiml2_loop;
	private $has_aiml2_interval;
	private $has_aiml_javascript;
	private $has_aiml_system;
	private $has_multi_pattern_cats;
	private $has_tag;
	
	
	const STATE_FILE = 1;
	const STATE_AIML = 2;
	const STATE_AIML_TOPIC = 3;
	const STATE_CATEGORY = 4;
	const STATE_PATTERN = 5;
	const STATE_THAT = 6;
	const STATE_CATEGORY_TOPIC = 7;
	const STATE_TEMPLATE = 8;
	
	const IMPORT_TIMEOUT_EXTEND = 60; /* 1-minute extension to PHP script timeout */
	
	
	private function error($in_error)
	{
		if ($this->_error !== '') return;
		$this->_error = 'AIML Error: Line '.
			xml_get_current_line_number($this->xml_parser).': '.$in_error;
	}
	
	
	private function notice($in_notice)
	{
		$this->notices[] = $in_notice;
	}
	

	public function _element_start($in_parser, $in_name, $in_attrs)
	{
		set_time_limit( JxBotAimlImport::IMPORT_TIMEOUT_EXTEND );
		//print '< '.$in_name.'<br>';
		switch ($this->state)
		{	
		case JxBotAimlImport::STATE_FILE:
			if ($in_name == 'aiml')
			{
				$this->state = JxBotAimlImport::STATE_AIML;
				if (isset($in_attrs['version']))
					$this->aiml_version = trim($in_attrs['version']);
				else $this->aiml_version = '1.0';
				
				$this->aiml_topic = '*';
			}
			else $this->error('Not an AIML file.');
			break;
		case JxBotAimlImport::STATE_AIML:
			if ($in_name == 'topic')
			{
				$this->state = JxBotAimlImport::STATE_AIML_TOPIC;
			}
			else if ($in_name == 'category')
			{
				$this->state = JxBotAimlImport::STATE_CATEGORY;
				$this->cat_topic = null; // overrides aiml_topic if not NULL
				$this->cat_that = '*';
				$this->cat_patterns = array();
				$this->cat_templates = array();
			}
			else $this->unrecognised[$in_name] = true;
			break;
		case JxBotAimlImport::STATE_CATEGORY:
			if ($in_name == 'pattern')
			{
				$this->state = JxBotAimlImport::STATE_PATTERN;
				$this->content = '';
			}
			else if ($in_name == 'template')
			{
				$this->state = JxBotAimlImport::STATE_TEMPLATE;
				$this->content = '';
			}
			else if ($in_name == 'that')
			{
				$this->state = JxBotAimlImport::STATE_THAT;
			}
			else if ($in_name == 'topic')
			{
				$this->state = JxBotAimlImport::STATE_CATEGORY_TOPIC;
			}
			else $this->unrecognised[$in_name] = true;
			break;
		case JxBotAimlImport::STATE_PATTERN:
			if (($in_name != 'bot') && ($in_name != 'set') && ($in_name != 'name'))
				$this->unrecognised[$in_name] = true;
			else
			{
				$this->content .= '<'.$in_name;
				foreach ($in_attrs as $name => $value)
					$this->content .= ' '.$name.'="'.$value.'"';
				$this->content .= '>';
			}
			break;
		case JxBotAimlImport::STATE_TEMPLATE:
			if (!in_array($in_name, $this->recognised_template_tags))
				$this->unrecognised[$in_name] = true;
			if ($in_name == 'tag')
				$this->has_tag = true;
			else if (($in_name == 'learn') && ($this->aiml_version == '1.0'))
				$this->has_aiml1_learn = true;
			else if (($in_name == 'learn') || ($in_name == 'learnf'))
				$this->has_aiml2_learn = true;
			else if ($in_name == 'gossip')
				$this->has_aiml1_gossip = true;
			else if ($in_name == 'sraix')
				$this->has_aiml2_sraix = true;
			else if ($in_name == 'loop')
				$this->has_aiml2_loop = true;
			else if ($in_name == 'interval')
				$this->has_aiml2_interval = true;
			else if ($in_name == 'javascript')
				$this->has_aiml_javascript = true;
			else if ($in_name == 'system')
				$this->has_aiml_system = true;
				
			$this->content .= '<'.$in_name;
			foreach ($in_attrs as $name => $value)
				$this->content .= ' '.$name.'="'.$value.'"';
			$this->content .= '>';
			break;
		}
	}
	
	
	public function _element_end($in_parser, $in_name)
	{
		set_time_limit( JxBotAimlImport::IMPORT_TIMEOUT_EXTEND );
		//print '/ '.$in_name.'  '.JxBotAiml::$state.'<br>';
		switch ($this->state)
		{	
		case JxBotAimlImport::STATE_CATEGORY:
			if ($in_name == 'category')
			{
				$this->state = JxBotAimlImport::STATE_AIML;
				$topic = trim(($this->cat_topic === null ? $this->aiml_topic : $this->cat_topic));
				$that = trim($this->cat_that);
				$category_id = JxBotNLData::category_new($that, $topic);
				foreach ($this->cat_patterns as $pattern)
				{
					JxBotNLData::pattern_add($category_id, trim($pattern), $that, $topic);
				}
				foreach ($this->cat_templates as $template)
				{
					JxBotNLData::template_add($category_id, $template);
				}
				if ((count($this->cat_patterns) > 1) || (count($this->cat_templates) > 1))
					$this->has_multi_pattern_cats = true;
			}
			break;
		case JxBotAimlImport::STATE_AIML_TOPIC:
			if ($in_name == 'topic')
				$this->state = JxBotAimlImport::STATE_AIML;
			break;
		case JxBotAimlImport::STATE_CATEGORY_TOPIC:
			if ($in_name == 'topic')
				$this->state = JxBotAimlImport::STATE_CATEGORY;
			break;
		case JxBotAimlImport::STATE_THAT:
			if ($in_name == 'that')
				$this->state = JxBotAimlImport::STATE_CATEGORY;
			break;
		case JxBotAimlImport::STATE_PATTERN:
			if ($in_name == 'pattern')
			{
				$this->cat_patterns[] = $this->content;
				$this->state = JxBotAimlImport::STATE_CATEGORY;
			}
			else if (($in_name == 'bot') || ($in_name == 'set') || ($in_name == 'name'))
				$this->content .= '</'.$in_name.'>';
			break;
		case JxBotAimlImport::STATE_TEMPLATE:
			if ($in_name == 'template')
			{
				$this->cat_templates[] = $this->content;
				$this->state = JxBotAimlImport::STATE_CATEGORY;
			}
			else $this->content .= '</'.$in_name.'>';
			break;
		}
	}
	
	
	public function _element_data($in_parser, $in_data)
	{
		set_time_limit( JxBotAimlImport::IMPORT_TIMEOUT_EXTEND );
		//print '=>'.$in_data.'<br>';
		switch ($this->state)
		{
		case JxBotAimlImport::STATE_AIML_TOPIC:
			$in_data = trim($in_data);
			if ($in_data == '') $in_data = '*';
			$this->aiml_topic = $in_data;
			break;
		case JxBotAimlImport::STATE_CATEGORY_TOPIC:
			$in_data = trim($in_data);
			if ($in_data == '') $in_data = '*';
			$this->cat_topic = $in_data;
			break;
		case JxBotAimlImport::STATE_THAT:
			$this->cat_that = $in_data;
			break;
		case JxBotAimlImport::STATE_PATTERN:
			$this->content .= $in_data;
			break;
		case JxBotAimlImport::STATE_TEMPLATE:
			$this->content .= $in_data;
			break;
		}
	}
	
	
	public function __construct()
	{
		$this->xml_parser = xml_parser_create();
		xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($this->xml_parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
		
		xml_set_object($this->xml_parser, $this);
		
		xml_set_element_handler($this->xml_parser, '_element_start', '_element_end');
		xml_set_character_data_handler($this->xml_parser, '_element_data');
		
		$this->recognised_template_tags = array(
			'bot', 'get', 'set', 'tag', 'name', 'index', 'srai', 'sraix',
			'learn', 'learnf', 'eval', 'gossip', 'javascript', 'condition', 'li',
			'random', 'system', 'date', 'star', 'thatstar', 'topicstar',
			'input', 'that', 'response', 'request', 'map', 'sr', 'id', 'size',
			'version', 'program', 'uppercase', 'lowercase', 'formal', 'sentence',
			'think', 'gender', 'person', 'person2', 'loop', 'interval', 'explode',
			'normalize', 'denormalize', 'vocabulary'
		);
	}
	
	
	public function __destruct()
	{
		if ($this->xml_parser) xml_parser_free($this->xml_parser);
		$this->xml_parser = null;
	}
	
	
	private function reset()
	{
		$this->error = '';
		$this->notices = array();
		
		$this->state = JxBotAimlImport::STATE_FILE;
		
		/* unrecognised (ignored) tags: */
		$this->unrecognised = array();
		
		/* unsupported AIML 1 features: */
		$this->has_aiml1_learn = false;
		$this->has_aiml1_gossip = false;
		
		/* unsupported AIML 2 features: */
		$this->has_aiml2_learn = false;
		$this->has_aiml2_sraix = false;
		$this->has_aiml2_loop = false;
		$this->has_aiml2_interval = false;
		
		/* unsupported AIML features: */
		$this->has_aiml_javascript = false;
		$this->has_aiml_system = false;
		
		/* JxBot features: */
		$this->has_multi_pattern_cats = false;
		$this->has_tag = false;
	}
	
	
	public function import($in_filename)
	{
		/* reset the importer */
		$this->reset();
		
		/* open the file */
		$fh = fopen($in_filename, 'r');
		if (!$fh) return "Server Error: Couldn't open AIML file.";
		
		/* parse the file */
		while ($data = fread($fh, 4096))
		{
			if (! xml_parse($this->xml_parser, $data, feof($fh)) )
			{
				$this->error( xml_error_string(xml_get_error_code($parser)) );
				break;
			}
		}
		xml_parse($this->xml_parser, '', true);
		fclose($fh);
		
		/* report errors */
		if ($this->_error != '') return $this->_error;
		
		/* prepare additional notices */
		if (count($this->unrecognised) > 0)
			$this->notice('The following unrecognised tags were ignored: '.
				implode(', ', array_keys($this->unrecognised)));
		
		if ($this->has_aiml1_learn)
			$this->notice('This AIML file expects the AIML 1.0 semantics of the learn tag, which are not supported by JxBot.');
		if ($this->has_aiml1_gossip)
			$this->notice('This AIML file utilises the old AIML 1.0 gossip tag, which is not supported by JxBot.');
		
		if ($this->has_aiml2_learn)
			$this->notice('This AIML file utilises the AIML 2.0 learn feature which is not yet supported by JxBot.');
		if ($this->has_aiml2_sraix)
			$this->notice('This AIML file utilises the AIML 2.0 sraix feature which is not yet supported by JxBot.');
		if ($this->has_aiml2_loop)
			$this->notice('This AIML file utilises the AIML 2.0 loop feature which is not yet supported by JxBot.');
		if ($this->has_aiml2_interval)
			$this->notice('This AIML file utilises the AIML 2.0 interval tag which is not yet supported by JxBot.');
		
		if ($this->has_aiml_javascript)
			$this->notice('This AIML file utilises the AIML server-side javascript feature, which is not supported by JxBot.');
		if ($this->has_aiml_system)
			$this->notice('This AIML file utilises the AIML system call feature, which is not yet supported by JxBot.');
			
		if ($this->has_multi_pattern_cats)
			$this->notice('This AIML file utilises the JxBot consolidated category representation, which may not be compatible with other AIML interpreters.');
		
		if ($this->has_tag)
			$this->notice('This AIML file utilises the JxBot tag feature, which may not be compatible with other AIML interpreters.');
		
		/* return the result */
		return $this->notices;
	}
}



// This needs revision and is now only being used for template parsing:

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
	
	private static $unnested_info;
	
	private static $error;
	private static $error_line;
	
	private static $root;
	private static $stack;
	
	
	private static function set_error($in_parser, $in_error)
	{
		if (JxBotAiml::$error === '')
		{
			JxBotAiml::$error = $in_error;
			JxBotAiml::$error_line = xml_get_current_line_number($in_parser);
		}
	}
	
/*
	public static function _element_start($in_parser, $in_name, $in_attrs)
	{
		//print '< '.$in_name.'<br>';
		switch (JxBotAiml::$state)
		{
		case JxBotAiml::STATE_TOP:
			if ($in_name == 'category')
			{
				JxBotAiml::$state = JxBotAiml::STATE_CAT;
				JxBotAiml::$category_id = JxBotNLData::category_new('*', JxBotAiml::$top_topic);
				JxBotAiml::$that = '*';
				JxBotAiml::$topic = JxBotAiml::$top_topic;
			}
			else if ($in_name == 'topic' && isset($in_attrs['name']))
				JxBotAiml::$top_topic = $in_attribs['name'];
			break;
		case JxBotAiml::STATE_CAT:
			//print $in_name.'<br>';
			if ($in_name == 'pattern')
			{
				JxBotAiml::$state = JxBotAiml::STATE_PAT;
				JxBotAiml::$pattern = '';
			}
			elseif ($in_name == 'template')
			{
				JxBotAiml::$state = JxBotAiml::STATE_TMP;
				JxBotAiml::$template = '';
				//print 'entering template <br>';
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
			
		case JxBotAiml::STATE_TMP:
			JxBotAiml::$template .= '<'.$in_name;
			//var_dump($in_attrs);
			foreach ($in_attrs as $name => $value)
			{
				JxBotAiml::$template .= ' '.$name.'="'.$value.'"';
			}
			JxBotAiml::$template .= '>';
			break;
			
		
		}
	}
	
	
	public static function _element_end($in_parser, $in_name)
	{
		//print '/ '.$in_name.'  '.JxBotAiml::$state.'<br>';
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
				//print 'END patern<br>';
				JxBotAiml::$state = JxBotAiml::STATE_CAT;
				JxBotNLData::pattern_add(JxBotAiml::$category_id, JxBotAiml::$pattern,
					'*', JxBotAiml::$top_topic);
				//print 'end pattern<br>';
			}
			break;
		case JxBotAiml::STATE_TMP:
			if ($in_name == 'template')
			{
				//print 'creating template';
				JxBotAiml::$state = JxBotAiml::STATE_CAT;
				JxBotNLData::template_add(JxBotAiml::$category_id, JxBotAiml::$template);
			}
			else
			{
				JxBotAiml::$template .= '</'.$in_name.'>';
			}
			break;
		case JxBotAiml::STATE_THT:
			if ($in_name == 'that')
			{
				JxBotAiml::$state = JxBotAiml::STATE_CAT;
				JxBotNLData::category_update(JxBotAiml::$category_id, 
					JxBotAiml::$that,
					JxBotAiml::$topic);
			}
			break;
		case JxBotAiml::STATE_TPC:
			if ($in_name == 'topic')
			{
				JxBotAiml::$state = JxBotAiml::STATE_CAT;
				JxBotNLData::category_update(JxBotAiml::$category_id, 
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
			
		case JxBotAiml::STATE_THT:
			JxBotAiml::$that .= $in_data;
			break;
		case JxBotAiml::STATE_TPC:
			JxBotAiml::$topic .= $in_data;
			break;
	
		}
	}*/
	
	
	private static function parser_create($in_start, $in_end, $in_data)
	{
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
		
		xml_set_element_handler($parser, 
			array('JxBotAiml', $in_start), 
			array('JxBotAiml', $in_end));
		xml_set_character_data_handler($parser, 
			array('JxBotAiml', $in_data));
			
		return $parser;
	}
	
	/*
	public static function import($in_filename)
	{
		set_time_limit(300); // 5-minutes
		
		$parser = JxBotAiml::parser_create('_element_start', '_element_end', '_element_data');
			
		JxBotAiml::$state = JxBotAiml::STATE_TOP;
		JxBotAiml::$top_topic = '*';
		
		$fh = fopen($in_filename, 'r');
		if (!$fh) return "Server upload configuration error.  Couldn't open temporary file.";
		
		while ($data = fread($fh, 4096))
		{
			if (! xml_parse($parser, $data, feof($fh)) )
			{
				print 'ERROR';
				return 'AIML error: Line ' . xml_get_current_line_number($parser) . ': ' . 
						xml_error_string(xml_get_error_code($parser));
			}
		}
		
		xml_parse($parser, '', true);
		
		fclose($fh);
		xml_parser_free($parser);
		
		return true;
	}
	
	
	*/
	
	
	
	private static function _template_open($in_parser, $in_name, $in_attrs)
	{
		$element = JxBotAiml::$stack[] = new JxBotElement($in_name, $in_attrs);
		if (count(JxBotAiml::$stack) > 1)
			JxBotAiml::$stack[count(JxBotAiml::$stack) - 2]->children[] = $element;
		else
			JxBotAiml::$root = $element;
		
		/*foreach ($in_attrs as $name => $value)
		{
			$attr_element = new JxBotElement($name);
			$element->children[] = $attr_element;
			$attr_element->children[] = $value;
		}*/
	}
	
	
	private static function _template_close($in_parser, $in_name)
	{
		array_pop(JxBotAiml::$stack);
	}
	
	
	private static function _template_data($in_parser, $in_content)
	{
		JxBotAiml::$stack[count(JxBotAiml::$stack) - 1]->children[] = $in_content;
	}
	
	
	public static function parse_template($in_template)
	{
		JxBotAiml::$error = '';
		JxBotAiml::$root = NULL;
		JxBotAiml::$stack = array();
		
		$parser = JxBotAiml::parser_create('_template_open', '_template_close', '_template_data');
		$in_template = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><template>" . $in_template . '</template>';
		xml_parse($parser, $in_template, true);
		xml_parser_free($parser);

		return JxBotAiml::$root;
	}
	
}

