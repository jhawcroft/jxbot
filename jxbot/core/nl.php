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

/* natural language auxiliary functions & utilities;
normalisation, substitution, tense, spelling, etc. */

if (!defined('JXBOT')) die('Direct script access not permitted.');


class JxBotNL
{

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
	
	
/********************************************************************************
String Comparison
*/

	public static function strings_equal($in_string1, $in_string2)
	{
		return (mb_strtolower($in_string1) == mb_strtolower($in_string2));
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
	
	
	public static function normalise($in_input, $in_keep_wildcards = false)
	/* splits the user input into words, removes punctuation, folds the case for 
	optimal matching and strips accents */
	{
		if ($in_keep_wildcards) return JxBotNL::normalise_pattern($in_input);
	
		$output = $in_input;
		
		$output = JxBotNL::upper($output);
		
		/* ! not sure about hard-coding this;
	         probably this ought to be handled through AIML substitutions so that
	         foreign language support isn't problematic? */
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




