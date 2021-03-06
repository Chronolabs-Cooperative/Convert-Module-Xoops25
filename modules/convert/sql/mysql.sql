CREATE TABLE `convert_files` (
  `id` mediumint(96) NOT NULL AUTO_INCREMENT,
  `fontid` mediumint(12) NOT NULL DEFAULT '0',
  `uploadid` mediumint(12) NOT NULL DEFAULT '0',
  `path` varchar(128) NOT NULL DEFAULT '',
  `file` varchar(128) NOT NULL DEFAULT '',
  `extension` varchar(15) NOT NULL DEFAULT '',
  `bytes` int(12) NOT NULL DEFAULT '0',
  `md5` varchar(32) NOT NULL DEFAULT '',
  `sha1` varchar(44) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `SEARCH` (`fontid`,`uploadid`,`file`,`extension`,`md5`,`sha1`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `convert_fonts` (
  `id` mediumint(12) NOT NULL AUTO_INCREMENT,
  `uploadid` mediumint(12) NOT NULL DEFAULT '0',
  `identity` varchar(32) NOT NULL DEFAULT '',
  `year` int(4) NOT NULL DEFAULT '0',
  `month` int(2) NOT NULL DEFAULT '0',
  `daynum` int(2) NOT NULL DEFAULT '0',
  `day` enum('Sun','Mon','Tue','Wed','Thu','Fri','Sat') NOT NULL DEFAULT 'Sat',
  `week` int(2) NOT NULL DEFAULT '0',
  `hour` int(2) NOT NULL DEFAULT '0',
  `uid` int(13) NOT NULL DEFAULT '0',
  `glyphs` int(16) NOT NULL DEFAULT '0',
  `comments` int(16) NOT NULL DEFAULT '0',
  `storage` enum('XOOPS_DATA','XOOPS_UPLOADS') NOT NULL DEFAULT 'XOOPS_DATA',
  `path` varchar(255) NOT NULL DEFAULT '',
  `pack` varchar(128) NOT NULL DEFAULT '',
  `fontfile` varchar(255) NOT NULL DEFAULT '',
  `syndicated` enum('Yes','No') NOT NULL DEFAULT 'No',
  `sydnication` varchar(255) NOT NULL DEFAULT '',
  `sydnicating` float(24,8) NOT NULL DEFAULT '0.00000000',
  `cachefile` varchar(255) NOT NULL DEFAULT '',
  `cached` float(24,8) NOT NULL DEFAULT '0.00000000',
  `accessed` float(24,8) NOT NULL DEFAULT '0.00000000',
  `deleted` float(24,8) NOT NULL DEFAULT '0.00000000',
  `zip-bytes` int(14) NOT NULL DEFAULT '0',
  `zip-files` int(14) NOT NULL DEFAULT '0',
  `open-bytes` int(14) NOT NULL DEFAULT '0',
  `downloads` int(16) NOT NULL DEFAULT '0',
  `downloaded` float(24,8) NOT NULL DEFAULT '0.00000000',
  `kb-downloaded` float(44,4) NOT NULL DEFAULT '0.0000',
  `previews` int(16) NOT NULL DEFAULT '0',
  `previewed` float(24,8) NOT NULL DEFAULT '0.00000000',
  `kb-previewed` float(44,4) NOT NULL DEFAULT '0.0000',
  `kb-glyphed` float(44,4) NOT NULL DEFAULT '0.0000',
  `name` varchar(255) NOT NULL DEFAULT '',
  `version` float(8,4) NOT NULL DEFAULT '0.0000',
  `license` varchar(255) NOT NULL DEFAULT '',
  `licensecode` varchar(8) NOT NULL DEFAULT '',
  `tags` varchar(255) NOT NULL DEFAULT '',
  `referee` varchar(16) NOT NULL DEFAULT '',
  `barcode` varchar(8) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `SEARCH` (`uploadid`,`storage`,`fontfile`,`referee`,`barcode`,`name`,`identity`,`syndicated`,`glyphs`,`uid`,`comments`,`accessed`,`deleted`,`cached`),
  KEY `HISTORY` (`year`,`month`,`daynum`,`day`,`week`,`hour`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `convert_glyphs` (
  `id` mediumint(64) NOT NULL AUTO_INCREMENT,
  `fontid` mediumint(12) NOT NULL DEFAULT '0',
  `value` int(8) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `SEARCH` (`value`,`fontid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `convert_uploads` (
  `id` mediumint(12) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) NOT NULL DEFAULT '',
  `file` varchar(128) NOT NULL DEFAULT '',
  `file-bytes` int(14) NOT NULL DEFAULT '0',
  `extension` varchar(10) NOT NULL DEFAULT '',
  `converted` enum('Yes','No') NOT NULL DEFAULT 'No',
  `reported` enum('Yes','No') NOT NULL DEFAULT 'No',
  `tweeted` enum('Yes','No','Unsupported') NOT NULL DEFAULT 'Unsupported',
  `fontid` mediumint(12) NOT NULL DEFAULT '0',
  `uploading` float(24,8) NOT NULL DEFAULT '0.00000000',
  `downloading` float(24,8) NOT NULL DEFAULT '0.00000000',
  `reporting` float(24,8) NOT NULL DEFAULT '0.00000000',
  `tweeting` float(24,8) NOT NULL DEFAULT '0.00000000',
  `uid` int(12) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `company` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `twitter` varchar(42) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `SEARCH` (`file`,`extension`,`converted`,`fontid`,`reported`,`tweeted`,`uploading`,`downloading`,`reporting`,`tweeting`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;