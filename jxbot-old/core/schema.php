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
 
// create the schema

$jxbot_db->exec('
CREATE TABLE category (
    id BIGINT AUTO_INCREMENT NOT NULL PRIMARY KEY
)
');

/* type will influence the behaviour of the matching;
0 => match everything, but don't worry about stuff that doesn't match,
1 => AIML mode, match everything exactly as specified by the pattern */
$jxbot_db->exec('
CREATE TABLE sequence (
	type TINYINT NOT NULL,
	sequence_id BIGINT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    category_id BIGINT NOT NULL REFERENCES category (id),
    words VARCHAR(255) NOT NULL,
    length TINYINT NOT NULL,
    that VARCHAR(255) NOT NULL,
    topic VARCHAR(255) NOT NULL,
    sort_key VARCHAR(255) NOT NULL,
    INDEX(category_id)
)
');

// can double as a dictionary/theasurus-base/etc. and be used throughout the schema
$jxbot_db->exec('
CREATE TABLE word (
	word_id BIGINT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    word VARCHAR(30) NOT NULL,
    UNIQUE(word)
)
');

// inefficient duplication of words, but, might be just as well to use as is?
$jxbot_db->exec('
CREATE TABLE sequence_word (
	sequence_id BIGINT NOT NULL REFERENCES sequence (sequence_id),
    word_id BIGINT NOT NULL REFERENCES word (word_id),
   PRIMARY KEY (sequence_id, word_id)
)
');

$jxbot_db->exec('
CREATE TABLE template (
	category_id BIGINT NOT NULL REFERENCES sequence (sequence_id),
    template_id BIGINT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    template TEXT,
    INDEX (category_id)
)
');

$jxbot_db->exec('
CREATE TABLE log (
	id BIGINT AUTO_INCREMENT NOT NULL PRIMARY KEY,
	input TEXT NOT NULL,
	output TEXT NOT NULL,
	convo_id TEXT NOT NULL,
	stamp TIMESTAMP NOT NULL
)
');

$jxbot_db->exec('
CREATE TABLE opt (
	opt_key VARCHAR(100) NOT NULL PRIMARY KEY,
	opt_value VARCHAR(100) NOT NULL
)
');







