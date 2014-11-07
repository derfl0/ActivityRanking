<?php

class VariableScoreTables extends DBMigration {

    function up() {
        DBManager::get()->exec("CREATE TABLE `user_activity_tables` (
  `table` varchar(255) NOT NULL DEFAULT '',
  `datecol` varchar(255) DEFAULT NULL,
  `usercol` varchar(255) DEFAULT NULL,
  `where` varchar(4096) DEFAULT NULL,
  PRIMARY KEY (`table`)
);");

        DBManager::get()->exec("INSERT INTO `user_activity_tables` (`table`, `datecol`, `usercol`, `where`)
        VALUES
	('blubber',NULL,NULL,NULL),
	('comments',NULL,NULL,NULL),
	('dokumente',NULL,NULL,NULL),
	('forum_entries',NULL,NULL,NULL),
	('kategorien',NULL,'range_id',NULL),
	('message',NULL,'autor_id',NULL),
	('news',NULL,NULL,NULL),
	('seminar_user',NULL,NULL,NULL),
	('user_info',NULL,NULL,NULL),
	('vote',NULL,'range_id',NULL),
	('voteanswers_user','votedate',NULL,NULL),
	('vote_user','votedate',NULL,NULL),
	('wiki','chdate',NULL,NULL);");
    }

    function down() {
        DBManager::get()->exec("DROP TABLE `user_activity_tables`");
    }

}
