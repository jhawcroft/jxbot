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

/* creation of the JxBot MySQL database schema and population with most essential
default values except those explicitly configured by the install script */

if (!defined('JXBOT')) die('Direct script access not permitted.');


class JxBotSchema
{

	public static function install()
	{
	
/* Table Definitions: */

JxBotDB::$db->exec('
CREATE TABLE file (
    id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    status VARCHAR(15) NOT NULL,
    last_update TIMESTAMP,
    UNIQUE(name)
) 
ENGINE=MyISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;
');

JxBotDB::$db->exec('
CREATE TABLE aiml_log (
    id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
    file VARCHAR(100) NOT NULL,
    message VARCHAR(255) NOT NULL,
    level TINYINT(1) NOT NULL,
    stamp TIMESTAMP
) 
ENGINE=MyISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;
');

JxBotDB::$db->exec('
CREATE TABLE category (
    id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
    that VARCHAR(255) NOT NULL,
    topic VARCHAR(255) NOT NULL,
    file INT(11) NULL
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
	topic VARCHAR(255) NOT NULL,
	term_count TINYINT NOT NULL
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
	INDEX(session),
	INDEX(stamp)
)
ENGINE=InnoDB
CHARACTER SET utf8
COLLATE utf8_general_ci;
');

JxBotDB::$db->exec('
CREATE TABLE stats (
	interactions INT(11) NOT NULL DEFAULT 0
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
	phrase VARCHAR(150) NOT NULL PRIMARY KEY,
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


/* Data Initialization */

JxBotDB::$db->exec("
INSERT INTO pattern_node (id, parent, expression, sort_key, is_terminal) 
VALUES (0, 0, ':ROOT:', 0, 0);
");

JxBotDB::$db->exec("
INSERT INTO stats (interactions) VALUES (0);
");

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

INSERT INTO _map (id, name) VALUES (4, 'substitutions');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' CAN''T ', ' CAN NOT ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' COULDN''T ', ' COULD NOT ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' ISN''T ', ' IS NOT ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' YOU''RE ', ' YOU ARE ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' IT''S ', ' IT IS ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' YOU''VE ', ' YOU HAVE ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' YOU''LL ', ' YOU WILL ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' DID''NT ', ' DID NOT ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' AINT ', ' IS NOT ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' AIN''T ', ' IS NOT ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' ISN''T ', ' IS NOT ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' ISNT ', ' IS NOT ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' AREN''T ', ' ARE NOT ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' ARENT ', ' ARE NOT ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' WHERE''S ', ' WHERE IS ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' HAVEN''T ', ' HAVE NOT ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' HASN''T ', ' HAS NOT ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' CAN''T ', ' CAN NOT ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' WHOS ', ' WHO IS ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' IT''S ', ' IT IS ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' HOW''S ', ' HOW IS ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' HOW''D ', ' HOW WOULD ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' HOWS ', ' HOW IS ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' WHATS ', ' WHAT IS ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' THERES ', ' THERE IS ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' THERE''S ', ' THERE IS ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' THATS ', ' THAT IS ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' DON''T ', ' DO NOT ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' WON''T ', ' WILL NOT ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' O K ', ' OK ');
INSERT INTO map_item (map, s_from, s_to) VALUES (4, ' OHH ', ' OHH ');



");
//INSERT INTO _map (id, name) VALUES (6, 'tags');
//INSERT INTO _map (id, name) VALUES (5, 'autocorrect'); <-- do something more efficient


JxBotDB::$db->exec("
INSERT INTO opt (opt_key, opt_value) VALUES ('def_species', 'human');


");

// remaining options will be added by the installer!

// age and birthday ought to be set & calculated from bot settings!

JxBotDB::$db->exec("
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_birthday', '2016/01/01');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_age', '1');

INSERT INTO opt (opt_key, opt_value) VALUES ('bot_kingdom', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_family', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_phylum', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_class', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_order', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_species', '');

INSERT INTO opt (opt_key, opt_value) VALUES ('bot_language', 'English');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_nationality', 'Australian');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_website', 'http://joshhawcroft.org/jxbot');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_email', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_master', 'Josh');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_botmaster', 'Josh');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_birthplace', 'Brisbane');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_location', 'Brisbane');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_sign', 'Capricorn');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_feeling', 'buzzed');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_emotion', 'peaceful');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_emotions', 'peace and happiness');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_arch', 'Intel');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_gender', 'male');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_name', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_party', 'non-aligned');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_religion', '');

INSERT INTO opt (opt_key, opt_value) VALUES ('bot_talkabout', 'non-duality');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favoritesport', 'soccer');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favoriteactor', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favoriteactress', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favoriteartist', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favoriteauthor', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favoritebook', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favoritemovie', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favoritesong', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favoritecolor', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favoriteband', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favoritefood', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favoritephilosopher', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favoriteshow', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favoritesubject', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favoritetea', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favorite', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_forfun', '');
INSERT INTO opt (opt_key, opt_value) VALUES ('bot_favoriteseason', '');


");
/*

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
*/

JxBotDB::$db->exec("
INSERT INTO opt (opt_key, opt_value) VALUES ('admin_user', 'admin');
INSERT INTO opt (opt_key, opt_value) VALUES ('admin_timeout', '60');
INSERT INTO opt (opt_key, opt_value) VALUES ('sys_cap_bot_ipm', '300');
");

JxBotDB::$db->exec('
CREATE TABLE login (
	id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
	stamp TIMESTAMP NOT NULL,
	username VARCHAR(30) NOT NULL,
	note VARCHAR(255),
	INDEX(stamp)
)
ENGINE=InnoDB
CHARACTER SET utf8
COLLATE utf8_general_ci;
');



	}
}
