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
	public $attrs;
	public $children;
	
	
	public function __construct($in_name, $in_attrs = null)
	{
		$this->name = $in_name;
		$this->attrs = $in_attrs;
		$this->children = array();
	}
	
	
	public function child_element_count($in_element_kind = null)
	{
		$count = 0;
		foreach ($this->children as $child)
		{
			if (!is_string($child)) 
			{
				if (($in_element_kind === null) || 
					($in_element_kind == $child->name))
					$count++;
			}
		}
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
	
	
	public function child_or_attr_named($in_context, $in_name, $in_default = '')
	{
		if (($this->attrs !== null) && isset($this->attrs[$in_name]))
			return $this->attrs[$in_name];
		else
		{
		
			$child = $this->child_with_name($in_name);
			if ($child !== null) return $child->text_value($in_context);
			else return $in_default;
		}
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
	
	
	public function flatten()
	{
		$content = '<'.$this->name;
		if ($this->attrs !== null)
		{
			foreach ($this->attrs as $name => $value)
			{
				$content .= ' '.$name.'="'.$value.'"';
			}
		}
		if (count($this->children) == 0)
			$content .= '/>';
		else
		{
			$content .= '>';
			foreach ($this->children as $child)
			{
				if (is_string($child)) $content .= $child;
				else $content .= $child->flatten();
			}
			$content .= '</'.$this->name.'>';
		}
		return $content;
	}
	
	
	public static function matches_simple_pattern(&$in_value, &$in_pattern)
	{
		if ($in_pattern === '*') /* Pandorabots and AIML 2.0 bound/unbound check */
		{
			if ($in_value === null) return false;
			else return true;
		}
		return JxBotNL::strings_equal($in_value, $in_pattern);
		// ** TODO - implement * wildcard and case-insensitive comparison for AIML 2.0
	}

	
	public function generate($in_context) 
	// ! TODO:  Will probably remove in context from all these functions
	// and just access the conversation directly; doesn't seem to be a very useful idea... ***
	{
		switch ($this->name)
		{
		case 'system': /* security implications; will not implement until reasonable
		                  controls are developed and an on/off switch */
		case 'javascript': /* server-side javascript is not implemented */
			break;
		
		case 'think':
			$this->text_value($in_context);
			break;
		
		case 'template':
		case 'gossip': /* doesn't do anything in this AIML interpreter
		                  and is removed in AIML 2.0 */
		case 'x-learn-1': /* AIML 1 learn tag to be filtered on import and contents
		                     passed through without further action */
			return $this->text_value($in_context);
		
		case 'random':
			$count = $this->child_element_count();
			$index = mt_rand(1, $count) - 1;
			return $this->child_element($index)->text_value($in_context);
		
		case 'condition':
			$count = $this->child_element_count('li');
			if ($count == 0)
			{
				$predicate = $this->child_or_attr_named($in_context, 'name');
				$value = JxBotConverse::predicate($predicate);
				$pattern = $this->child_or_attr_named($in_context, 'value');
				if (JxBotElement::matches_simple_pattern($value, $pattern))
					return $this->text_value($in_context, array('name', 'value'));
			}
			else 
			{
				$count = $this->child_element_count();
				$predicate = $this->child_or_attr_named($in_context, 'name', null);
				if ($predicate !== null)
				{
					$value = JxBotConverse::predicate($predicate);
					for ($i = 0; $i < $count; $i++)
					{
						$item = $this->child_element($i);
						$pattern = $item->child_or_attr_named($in_context, 'value', null);
						if ($pattern === null)
							return $item->text_value($in_context, array('value'));
						if (JxBotElement::matches_simple_pattern($value, $pattern))
							return $item->text_value($in_context, array('value'));
					}
				}
				else
				{
					for ($i = 0; $i < $count; $i++)
					{
						$item = $this->child_element($i);
						$predicate = $item->child_or_attr_named($in_context, 'name');
						$value = JxBotConverse::predicate($predicate);
						$pattern = $item->child_or_attr_named($in_context, 'value', null);
						if ($pattern === null)
							return $item->text_value($in_context, array('value','name'));
						if (JxBotElement::matches_simple_pattern($value, $pattern))
							return $item->text_value($in_context, array('value','name'));
					}
				}
			}
			break;
		
		case 'star':
		case 'thatstar':
		case 'topicstar':
			$index = intval( $this->child_or_attr_named($in_context, 'index', 1) );
			return 'STAR['.$index.']'; // ** TO PATCH
			break;
			
		case 'srai':
			 // ** TO PATCH
			break;
		
		case 'sr': /* srai star /srai */
			 // ** TO PATCH
			break;
		
		case 'bot':
		case 'get':
			$name = trim( $this->child_or_attr_named($in_context, 'name') );
			if ($name !== '')
			{
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
		case 'program':
			return JxBot::PROGRAM . ' ' . JxBot::VERSION;
			
		case 'set':
			$name = trim( $this->child_or_attr_named($in_context, 'name') );
			if ($name != '')
			{
				$value = $this->text_value($in_context, array('name'));
				JxBotConverse::set_predicate($name, $value);
				return $value;
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
		
		default: /* push unknown content & tags through to output */
			// ! REVIEW:  A better policy might be to tightly control which tags are
			//            allowed through, or provide an appropriate system option.
			return $this->flatten();
		}
	}
}




