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

/* output generation; converts a template to an output based upon the supplied
match information and current bot context */

if (!defined('JXBOT')) die('Direct script access not permitted.');


class JxBotElement 
{
	public $name;
	public $children;
	
	
	public function __construct($in_name)
	{
		$this->name = $in_name;
		$this->children = array();
	}
	
	
	public function child_element_count()
	{
		$count = 0;
		foreach ($this->children as $child)
			if (!is_string($child)) $count++;
		return $count;
	}
	
	
	public function child_element($in_index)
	{
		$index = 0;
		foreach ($this->children as $child)
		{
			if (!is_string($child))
			{
				if ($index == $in_index) return $child;
				$index++;
			}
		}
		return null;
	}
	
	
	public function child_with_name($in_name)
	{
		foreach ($this->children as $child)
		{
			if (!is_string($child))
			{
				if ($in_name == $child->name) return $child;
			}
		}
		return null;
	}
	
	
	public function text_content()
	{
		$content = '';
		foreach ($this->children as $child)
			if (is_string($child)) $content .= $child;
		return $content;
	}
	
	
	public function text_value($in_context, $in_ignore = null)
	{
		$content = '';
		foreach ($this->children as $child)
		{
			if (is_string($child)) $content .= $child;
			else 
			{
				if ( ($in_ignore !== null) && (in_array($child->name, $in_ignore)) ) ;
				else $content .= $child->generate($in_context);
			}
		}
		return $content;
	}
	
	
	public function generate($in_context) // pass in a Converse instance / access statically demand
	{
		switch ($this->name)
		{
		case 'template':
			return $this->text_value($in_context);
		
		case 'random':
			$count = $this->child_element_count();
			$index = mt_rand(1, $count) - 1;
			return $this->child_element($index)->text_value($in_context);
		
		case 'star':
		case 'thatstar':
		case 'topicstar':
			if (count($this->children) == 1 &&
				is_object($this->children[0]) &&
				$this->children[0]->name == 'index')
			{
				$index = intval( $this->children[0]->text_value($in_context) );
				return 'STAR['.$index.']'; // ** TO PATCH
			}
			break;
		
		case 'bot':
		case 'get':
			if (count($this->children) == 1 &&
				is_object($this->children[0]) &&
				$this->children[0]->name == 'name')
			{
				$name = trim( $this->children[0]->text_value($in_context) );
				if ($this->name == 'get')
					return JxBotConverse::predicate($name);
				else
					return JxBotConfig::predicate($name);
			}
			break;	
		case 'id':
			return JxBotConverse::predicate('id');
		case 'size':
			/* we return the number of patterns, which is equivalent to AIML standard
			category count; since in JxBot one category != one pattern. */
			return JxBotNLData::pattern_count();
		case 'version':
			return JxBot::VERSION;
			
		case 'set':
			$name_element = $this->child_with_name('name');
			if ($name_element !== null)
			{
				$name = trim( $name_element->text_value($in_context) );
				$value = $this->text_value($in_context, array('name'));
				if ($name != '')
				{
					JxBotConverse::set_predicate($name, $value);
					return $value;
				}
			}
			break;
			
		case 'date':
			$php_format = 'r'; /* default - AIML 1.0 - we specify date & time format */
			return date($php_format);
			
		case 'uppercase':
			return JxBotNL::upper( $this->text_value($in_context) );
		case 'lowercase':
			return JxBotNL::lower( $this->text_value($in_context) );
		case 'formal':
			return JxBotNL::formal( $this->text_value($in_context) );
		case 'sentence':
			return JxBotNL::sentence( $this->text_value($in_context) );
		}
	}
}




