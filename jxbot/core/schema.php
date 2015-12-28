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

/* creation of the JxBot MySQL database schema and population with most essential
default values except those explicitly configured by the install script */

if (!defined('JXBOT')) die('Direct script access not permitted.');


class JxBotSchema
{

	public static function install()
	{
	
JxBotDB::$db->exec('
CREATE TABLE category (
    id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
    that VARCHAR(255) NOT NULL,
    topic VARCHAR(255) NOT NULL
) 
ENGINE=MyISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;
');

JxBotDB::$db->exec('
CREATE TABLE pattern (
	id INT(11) NOT NULL PRIMARY KEY,
	category INT(11) NOT NULL,
	value VARCHAR(255) NOT NULL,
	that VARCHAR(255) NOT NULL,
	topic VARCHAR(255) NOT NULL
) 
ENGINE=MyISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;
');

JxBotDB::$db->exec('
CREATE TABLE pattern_node (
	id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
	parent INT(11) NOT NULL,
	expression VARCHAR(30) NOT NULL,
	sort_key TINYINT(1) NOT NULL,
	is_terminal TINYINT(1) NOT NULL DEFAULT 0,
	UNIQUE(parent,expression)
) 
ENGINE=MyISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;
');


JxBotDB::$db->exec("
INSERT INTO pattern_node (id, parent, expression, sort_key, is_terminal) 
VALUES (0, 0, ':ROOT:', 0, 0);
");


JxBotDB::$db->exec('
CREATE TABLE template (
	id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
	category INT(11) NOT NULL,
    template TEXT,
    INDEX (category)
) 
ENGINE=MyISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;
');


JxBotDB::$db->exec('
CREATE TABLE session (
	id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
	convo_id VARCHAR(100) NOT NULL,
	name VARCHAR(50) NOT NULL,
	accessed TIMESTAMP NOT NULL,
	UNIQUE(convo_id)
)
ENGINE=InnoDB
CHARACTER SET utf8
COLLATE utf8_general_ci;
');

JxBotDB::$db->exec('
CREATE TABLE predicate (
	session INT(11) NOT NULL,
	name VARCHAR(100) NOT NULL,
	value VARCHAR(255) NOT NULL,
	PRIMARY KEY (session, name)
)
ENGINE=InnoDB
CHARACTER SET utf8
COLLATE utf8_general_ci;
');

JxBotDB::$db->exec('
CREATE TABLE log (
	id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
	session INT(11) NOT NULL,
	input TEXT NOT NULL,
	output TEXT NOT NULL,
	time_respond float not null,
	time_match float not null,
	time_service float not null,
	intel_score float not null,
	stamp TIMESTAMP NOT NULL,
	INDEX(session)
)
ENGINE=InnoDB
CHARACTER SET utf8
COLLATE utf8_general_ci;
');

JxBotDB::$db->exec('
CREATE TABLE opt (
	opt_key VARCHAR(100) NOT NULL PRIMARY KEY,
	opt_value VARCHAR(100) NOT NULL
)
ENGINE=MyISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;
');

JxBotDB::$db->exec('
CREATE TABLE word (
	id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
    word VARCHAR(30) NOT NULL,
    UNIQUE(word)
)
ENGINE=MyISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;
');

JxBotDB::$db->exec('
CREATE TABLE _set (
	id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
	name VARCHAR(50) NOT NULL,
	UNIQUE(name)
)
ENGINE=MyISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;
');

JxBotDB::$db->exec('
CREATE TABLE set_item (
	id INT(11) NOT NULL,
	phrase VARCHAR(150) NOT NULL,
	INDEX(id)
)
ENGINE=MyISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;
');

JxBotDB::$db->exec('
CREATE TABLE _map (
	id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
	name VARCHAR(50) NOT NULL,
	UNIQUE(name)
) 
ENGINE=MyISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;
');

JxBotDB::$db->exec('
CREATE TABLE map_item (
	id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
	map INT(11) NOT NULL,
	s_from VARCHAR(255) NOT NULL,
	s_to VARCHAR(255) NOT NULL,
	INDEX(map),
	INDEX(s_from)
) 
ENGINE=MyISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;
');

JxBotDB::$db->exec("
INSERT INTO _map (id, name) VALUES (1, 'gender');
INSERT INTO map_item (map, s_from, s_to) VALUES (1, 'he', 'she');
INSERT INTO map_item (map, s_from, s_to) VALUES (1, 'she', 'he');
INSERT INTO map_item (map, s_from, s_to) VALUES (1, 'his', 'hers');
INSERT INTO map_item (map, s_from, s_to) VALUES (1, 'hers', 'his');
INSERT INTO map_item (map, s_from, s_to) VALUES (1, 'him', 'her');
INSERT INTO map_item (map, s_from, s_to) VALUES (1, 'her', 'him');

INSERT INTO _map (id, name) VALUES (2, 'person');
INSERT INTO map_item (map, s_from, s_to) VALUES (2, 'I was', 'he or she was');
INSERT INTO map_item (map, s_from, s_to) VALUES (2, 'he was', 'I was');
INSERT INTO map_item (map, s_from, s_to) VALUES (2, 'she was', 'I was');
INSERT INTO map_item (map, s_from, s_to) VALUES (2, 'I am', 'he or she is');
INSERT INTO map_item (map, s_from, s_to) VALUES (2, 'me', 'him or her');
INSERT INTO map_item (map, s_from, s_to) VALUES (2, 'my', 'his or her');
INSERT INTO map_item (map, s_from, s_to) VALUES (2, 'myself', 'him or herself');
INSERT INTO map_item (map, s_from, s_to) VALUES (2, 'mine', 'his or hers');

INSERT INTO _map (id, name) VALUES (3, 'person2');
INSERT INTO map_item (map, s_from, s_to) VALUES (3, 'with you', 'with me');
INSERT INTO map_item (map, s_from, s_to) VALUES (3, 'with me', 'with you');
INSERT INTO map_item (map, s_from, s_to) VALUES (3, 'to you', 'to me');
INSERT INTO map_item (map, s_from, s_to) VALUES (3, 'to me', 'to you');
INSERT INTO map_item (map, s_from, s_to) VALUES (3, 'of you', 'of me');
INSERT INTO map_item (map, s_from, s_to) VALUES (3, 'of me', 'of you');
INSERT INTO map_item (map, s_from, s_to) VALUES (3, 'for you', 'for me');
INSERT INTO map_item (map, s_from, s_to) VALUES (3, 'for me', 'for you');
INSERT INTO map_item (map, s_from, s_to) VALUES (3, 'give you', 'give me');
INSERT INTO map_item (map, s_from, s_to) VALUES (3, 'give me', 'give you');
INSERT INTO map_item (map, s_from, s_to) VALUES (3, 'giving you', 'giving me');
INSERT INTO map_item (map, s_from, s_to) VALUES (3, 'giving me', 'giving you');
INSERT INTO map_item (map, s_from, s_to) VALUES (3, 'gave you', 'gave me');
INSERT INTO map_item (map, s_from, s_to) VALUES (3, 'gave me', 'gave you');
");


JxBotDB::$db->exec("
INSERT INTO opt (opt_key, opt_value) VALUES ('def_species', 'human');
");

// remaining options will be added by the installer!

JxBotDB::$db->exec("
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_gender', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_master', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_website', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_email', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_birthday', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_location', '');

INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favorite_sport', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favorite_team', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favorite_actor', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favorite_actress', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favorite_artist', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favorite_song', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favorite_musician', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favorite_band', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favorite_music_kind', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favorite_movie', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favorite_tv_show', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favorite_color', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favorite_food', '');
");

JxBotDB::$db->exec("
INSERT INTO opt (opt_key, opt_value) VALUES ('admin_user', 'admin');
");

JxBotDB::$db->exec('
CREATE TABLE logins (
	id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
	stamp TIMESTAMP NOT NULL,
	username VARCHAR(30) NOT NULL,
	remote_desc TEXT,
	INDEX(stamp)
)
ENGINE=InnoDB
CHARACTER SET utf8
COLLATE utf8_general_ci;
');



	}
}
