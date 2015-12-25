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

JxBotDB::$db->exec('
CREATE TABLE category (
    id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
    that VARCHAR(255) NOT NULL,
    topic VARCHAR(255) NOT NULL
) ENGINE=MyISAM;
');

JxBotDB::$db->exec('
CREATE TABLE pattern (
	id INT(11) NOT NULL PRIMARY KEY,
	category INT(11) NOT NULL,
	value VARCHAR(255) NOT NULL,
	that VARCHAR(255) NOT NULL,
	topic VARCHAR(255) NOT NULL
) ENGINE=MyISAM;
');

JxBotDB::$db->exec('
CREATE TABLE pattern_node (
	id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
	parent INT(11) NULL,
	expression VARCHAR(30) NOT NULL,
	sort_key TINYINT(1) NOT NULL,
	is_terminal TINYINT(1) NOT NULL DEFAULT 0,
	UNIQUE(parent,expression)
) ENGINE=MyISAM;
');

JxBotDB::$db->exec('
CREATE TABLE template (
	id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
	category INT(11) NOT NULL,
    template TEXT,
    INDEX (category)
) ENGINE=MyISAM;
');


$jxbot_db->exec('
CREATE TABLE session (
	id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
	convo_id VARCHAR(100) NOT NULL,
	name VARCHAR(50) NOT NULL,
	accessed TIMESTAMP NOT NULL,
	UNIQUE(convo_id)
)
');

$jxbot_db->exec('
CREATE TABLE log (
	id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
	session INT(11) NOT NULL,
	input TEXT NOT NULL,
	output TEXT NOT NULL,
	stamp TIMESTAMP NOT NULL
)
');

$jxbot_db->exec('
CREATE TABLE opt (
	opt_key VARCHAR(100) NOT NULL PRIMARY KEY,
	opt_value VARCHAR(100) NOT NULL
)
');

$jxbot_db->exec('
CREATE TABLE word (
	id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
    word VARCHAR(30) NOT NULL,
    UNIQUE(word)
)
');

$jxbot_db->exec('
CREATE TABLE _set (
	id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
	name VARCHAR(50) NOT NULL,
	UNIQUE(name)
)
');

$jxbot_db->exec('
CREATE TABLE set_item (
	id INT(11) NOT NULL,
	phrase VARCHAR(150) NOT NULL,
	INDEX(id)
)
');





