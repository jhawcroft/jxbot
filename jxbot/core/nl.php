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

/* natural language auxiliary functions & utilities;
normalisation, substitution, tense, spelling, etc. */

if (!defined('JXBOT')) die('Direct script access not permitted.');


class JxBotNL
{

	private static $subs = null;


/********************************************************************************
Case Folding
! Note:  Case folding operations in some languages are not reliably reversable.
         Minimise use of these functions.
*/
	public static function upper($in_input)
	/* translates the input to UPPER-CASE */
	{
		return mb_strtoupper($in_input);
	}
	
	
	public static function lower($in_input)
	/* translates the input to lower-case */
	{
		return mb_strtolower($in_input);
	}
	
	
	public static function formal($in_input)
	/* translates the input to Title-Case */
	{
		return mb_convert_case($in_input, MB_CASE_TITLE);
	}
	
	
	public static function explode($in_input)
	/* puts spaces between all the characters of a string */
	{
		$chars = preg_split('/(?<!^)(?!$)/u', $in_input); 
		return implode(' ', $chars);
	}
	
	
	public static function sentence($in_input)
	/* capitalises the first word of each sentence */
	{
		$sentences = JxBotNL::split_sentences($in_input);
		
		foreach ($sentences as &$sentence)
		{
			$sentence = JxBotNL::upper( mb_substr($sentence, 0, 1) ) . 
				JxBotNL::lower( mb_substr($sentence, 1) );
		}
		
		// ! REVIEW:  Not sure about the implementation; should it leave characters
		//            other than the first alone?
		
		return implode(' ', $sentences);
	}
	
	
	public static function template_normalize($in_input)
	{
		$output = $in_input;
		
		$output = JxBotNL::upper($output);
		$output = JxBotNL::strip_punctuation($output);
  		
  		return $output;
	}
	
	
	public static function template_denormalize($in_input)
	{
		$output = $in_input;
		
		$output = JxBotNL::lower($output);
		
		// ! TODO:  This should be more useful once substitutions are implemented,
		// contractions, etc.
		
		return $output;
	}
	
	
/********************************************************************************
String Comparison
*/

	public static function strings_equal($in_string1, $in_string2)
	{
		return (mb_strtolower($in_string1) == mb_strtolower($in_string2));
	}
	
	
/********************************************************************************
Maps
*/

	public static function remap($in_map, $in_value)
	/* converts the value using the specified map, or returns it as-is
	if the map doesn't have a corresponding mapping */
	{
		$in_value = trim($in_value);
		
		// ! TODO need to check collation & ensure case insensitivity ******
		$stmt = JxBotDB::$db->prepare(
			'SELECT s_to FROM map_item JOIN _map ON map_item.map=_map.id
			WHERE _map.name=? AND map_item.s_from = ?'
		);
		$stmt->execute(array( $in_map, $in_value ));
		$result = $stmt->fetchAll(PDO::FETCH_NUM);
		
		if (count($result) == 0) return $in_value;
		else return $result[0][0];
	}
	

/********************************************************************************
Normalisation
*/
	
	public static function split_sentences($in_input)
	/* splits the input into sentences, leaving any punctuation intact */
	{
		// ! TODO: can make this utilise a plug-in architecture & system option eventually
		return JxBotSplitterEnglish::split($in_input);
	}
	
	
	private static function strip_accents($in_utf8)
	/* strips accents from selected latin characters
	! Note:  Doesn't recognise all possible characters with accents and currently only
	         handles the most common - or more specifically - only those of the 
	         historical ISO-8859-1 (ISO-Latin-1) character set encoding. */
	{
		return strtr($in_utf8, array(
			'À' => 'A',
			'Á' => 'A',
			'Â' => 'A',
			'Ã' => 'A',
			'Ä' => 'A',
			'Å' => 'A',
			'Ç' => 'C',
			'È' => 'E',
			'É' => 'E',
			'Ê' => 'E',
			'Ë' => 'E',
			'Ì' => 'I',
			'Í' => 'I',
			'Î' => 'I',
			'Ï' => 'I',
			'Ñ' => 'N',
			'Ò' => 'O',
			'Ó' => 'O',
			'Ô' => 'O',
			'Õ' => 'O',
			'Ö' => 'O',
			'Ù' => 'U',
			'Ú' => 'U',
			'Û' => 'U',
			'Ü' => 'U',
			'Ý' => 'Y'
		));
	}
	
	
	public static function split_words($in_input)
	/* splits the input into words */
	{
		/* treat all whitespace the same */
		$whitespace = array("\t", "\n", "\r");
  		$output = str_replace($whitespace, ' ', $in_input);
  		
  		/* split the input */
		$output = explode(' ', $output);
		
		/* remove empty words that would be formed by multiple consecutive spaces */
		return array_values( array_diff($output, array('')) );
	}
	
	
	public static function strip_punctuation($in_input)
	/* removes known punctuation from the input */
	{
		$punctuation = array(',', '!', '?', '\'', '*', '$', '%', ':', ';', '.',
			'"', '@', '#', '^', '&', '(', ')', '_', '-', '+', '[', ']', '{', '}', '|',
			'/', '\\', '`', '~');
		return str_replace($punctuation, '', $in_input);
	}
	
	
	private static function load_substitutions()
	{
		$stmt = JxBotDB::$db->prepare('SELECT s_from,s_to FROM map_item JOIN _map ON map_item.map=_map.id 
			WHERE _map.name=? ORDER BY s_from');
		$stmt->execute(array( 'substitutions' ));
		$subs = array();
		$rows = $stmt->fetchAll(PDO::FETCH_NUM);
		foreach ($rows as $row)
			$subs[JxBotNL::upper($row[0])] = JxBotNL::upper($row[1]);
		JxBotNL::$subs = $subs;
	}
	
	
	private static function apply_substitutions($in_input)
	{
		if (JxBotNL::$subs == null) JxBotNL::load_substitutions();
		foreach (JxBotNL::$subs as $search => $replace)
		{
			$in_input = str_replace($search, $replace, $in_input);
		}
		return $in_input;
	}
	
	
	public static function normalise($in_input)
	/* splits the user input into words, removes punctuation, folds the case for 
	optimal matching and strips accents */
	{
		//if ($in_keep_wildcards) return JxBotNL::normalise_pattern($in_input);
	
		/* preparation */
		$output = ' '.$in_input.' '; // leading & trailing space to help with substitution matching
		// consider replacing vertical & unusual horizontal whitespace (tabs) with all spaces here
		$output = JxBotNL::upper($output);
		
		/* do `tagging` in a different routine; really a pre-normalisation function */
	
		
		/* `substitution` normalisations; substitutions, abbreviations, spelling */
		$output = JxBotNL::apply_substitutions($output);
		
		/* `pattern fitting` normalisations */
	    if (JxBotConfig::option('pre_strip_accents', 0) == 1)
			$output = JxBotNL::strip_accents($output);
		
		$output = JxBotNL::strip_punctuation($output);
  		$output = JxBotNL::split_words($output);
  		
		return $output;
	}
	
	
	public static function normalise_pattern($in_pattern)
	/* splits a pattern into words and folds the case appropriately for fast matching;
	! IMPORTANT:  This is NOT an AIML pattern; the input must be an intermediate-
	              internal pattern format (see file ENGINEEERING for more information). */
	{
		$output = $in_pattern;
		
		$output = JxBotNL::upper($output);
		$output = JxBotNL::strip_accents($output);
		
		/*
		TODO:
		Ought to consider stripping only selected punctuation here.
		*/
		
		$output = JxBotNL::split_words($output);
		
		return $output;
	}
}




