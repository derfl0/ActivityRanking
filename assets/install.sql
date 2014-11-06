CREATE TABLE `user_activity_score` (
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `score` int(11) NOT NULL DEFAULT '0',
  `public` tinyint(4) NOT NULL DEFAULT '0',
  `chdate` int(11) NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `score` (`score`)
);