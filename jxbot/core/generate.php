<?php
/*******************************************************************************
JxBot - conversational agent for the web
Copyright (c) 2015 Joshua Hawcroft

    May all beings have happiness and the cause of happiness.
    May all beings be free of suffering and the cause of suffering.
    May all beings rejoice in the happiness of others.
    May all beings abide in equanimity; free of attachment and delusion.

Permission is hereby granted, free of charge, to any person obtaining a copy of 
this software and associated documentation files (the "Software"), to deal in 
the Software without restriction, including without limitation the rights to 
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so, 
subject to the following conditions:

The above copyright notice, preamble and this permission notice shall be 
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR 
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER 
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN 
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
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
			if (($in_value === null) || ($in_value === '')) return false;
			else return true;
		}
		return JxBotNL::strings_equal($in_value, $in_pattern);
		// ** TODO - implement * wildcard and case-insensitive comparison for AIML 2.0
	}
	
	
	private function get_capture($in_context, $in_type, $in_index)
	{
		if ($in_type == 'star')
			$value = $in_context->input_capture($in_index - 1);
		else if ($in_type == 'thatstar')
			$value = $in_context->that_capture($in_index - 1);
		else
			$value = $in_context->topic_capture($in_index - 1);
		if ($value === null) return ''; // ** should be an error somewhere.... ?
		return JxBotNL::lower($value);
	}
	
	
	private static function indicies($in_value)
	{
		$result = array();
		$parts = explode(',', $in_value);
		foreach ($parts as $part)
		{
			$part = trim($part);
			if ($part == '') $result[] = 1;
			else $result[] = intval($part);
		}
		return $result;
	}

	
	public static function compute_age()
	/* calculates an approximate age in years/months for the bot */
	{
		$birthday = JxBotConfig::bot('birthday');
		if ($birthday == '') return '';
		
		$birthtime = strtotime(trim($birthday));
		$now = time();
		
		$age_in_months = round(($now - $birthtime) / 2628000);
		$age_in_years = round($age_in_months / 12);
		
		if ($age_in_years < 1)
		{
			if ($age_in_months <= 1) return 'less than 1 month';
			else return number_format($age_in_months, 0) . ' months';
		}
		else
		{
			if ($age_in_years == 1) return '1 year';
			else return $age_in_years . ' years';
		}
	}
	
	
	public static function birthday()
	{
		$birthday = JxBotConfig::bot('birthday');
		if ($birthday == '') return '';
		
		$birthtime = strtotime(trim($birthday));
		
		return date('F j, Y', $birthtime);
	}

	
	public function generate($in_context) 
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
			$loop = false; 
			// preparation for AIML 2 loop; will need to keep a stack & check the stack so it can be toggled
			// by a <loop/> element anywhere in the depth of the <li>
			do
			{
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
			} while ($loop);
			break;
		
		case 'star':
		case 'thatstar':
		case 'topicstar':
			$index = intval( $this->child_or_attr_named($in_context, 'index', 1) );
			return $this->get_capture($in_context, $this->name, $index);
			
		case 'srai':
			return JxBotConverse::srai( $this->text_value($in_context) );
		
		case 'sr': /* srai star /srai */
			return JxBotConverse::srai( $this->get_capture($in_context, 'star', 1) );
		
		case 'bot':
		case 'get':
			$name = trim( $this->child_or_attr_named($in_context, 'name') );
			if ($name !== '')
			{
				if ($this->name == 'get')
					return JxBotConverse::predicate($name);
				else
				{
					if ($name == 'size') return JxBotNLData::pattern_count();
					else if ($name == 'age') return JxBotElement::compute_age();
					else if ($name == 'birthday') return JxBotElement::birthday();
					else return JxBotConfig::predicate($name);
				}
			}
			break;	
			
		case 'tag': /* non-standard Tag feature */
			$name = trim( $this->child_or_attr_named($in_context, 'name') );
			return $in_context->tag_value( $name );
			
		case 'id':
			return JxBotConverse::predicate('id');
		case 'size':
			/* we return the number of patterns, which is equivalent to AIML standard
			category count; since in JxBot one category != one pattern. */
			return JxBotNLData::pattern_count();
		case 'vocabulary':
			return JxBotNLData::word_count();
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
			
		case 'explode':
			return JxBotNL::explode( $this->text_value($in_context) );
		case 'normalize':
			return JxBotNL::template_normalize( $this->text_value($in_context) );
		case 'denormalize':
			return JxBotNL::template_denormalize( $this->text_value($in_context) );
			
		case 'gender':
			if (count($this->children) == 0)
				return JxBotNL::remap('gender', $this->get_capture($in_context, 'star', 1) );
			return JxBotNL::remap('gender', $this->text_value($in_context));
		case 'person':
			if (count($this->children) == 0)
				return JxBotNL::remap('person', $this->get_capture($in_context, 'star', 1) );
			return JxBotNL::remap('person', $this->text_value($in_context));
		case 'person2':
			if (count($this->children) == 0)
				return JxBotNL::remap('person2', $this->get_capture($in_context, 'star', 1) );
			return JxBotNL::remap('person2', $this->text_value($in_context));
			
		case 'map':
			$map_name = $this->child_or_attr_named($in_context, 'name');
			return JxBotNL::remap($map_name, $this->text_value($in_context, array('name')));
		
		case 'that':
			$indicies = JxBotElement::indicies( $this->child_or_attr_named($in_context, 'index') );
			$in_response = (count($indicies) >= 1 ? $indicies[0] : 1);
			$in_sentence = (count($indicies) >= 2 ? $indicies[1] : 1);
		
			$response = JxBotConverse::history_response($in_response - 1);
			$sentences = JxBotNL::split_sentences($response);
			if ( ($in_sentence < 1) || ($in_sentence > count($sentences)) ) return '';
			return $sentences[$in_sentence - 1];
		
		case 'input':
			$indicies = JxBotElement::indicies( $this->child_or_attr_named($in_context, 'index') );
			$in_request = (count($indicies) >= 1 ? $indicies[0] : 1);
			$in_sentence = (count($indicies) >= 2 ? $indicies[1] : 1);
			
			$request = JxBotConverse::history_request($in_request - 1);
			$sentences = JxBotNL::split_sentences($request);
			if ( ($in_sentence < 1) || ($in_sentence > count($sentences)) ) return '';
			return $sentences[$in_sentence - 1];
			
		case 'request':
			$index = intval( $this->child_or_attr_named($in_context, 'index', 1) );
			return JxBotConverse::history_request($index - 1);
		case 'response':
			$index = intval( $this->child_or_attr_named($in_context, 'index', 1) );
			return JxBotConverse::history_response($index - 1);
		
		default: /* push unknown content & tags through to output */
			// ! REVIEW:  A better policy might be to tightly control which tags are
			//            allowed through, or provide an appropriate system option.
			return $this->flatten();
		}
	}
}




