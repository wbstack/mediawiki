CREATE DATABASE IF NOT EXISTS `<<REPLACE_DATABASE>>`;
GRANT ALL ON `<<REPLACE_DATABASE>>`.* TO 'mwu_someuser'@'%';

USE <<REPLACE_DATABASE>>;

-- Adminer 4.6.3 MySQL dump
CREATE TABLE `<<REPLACE_PREFIX>>_account_credentials` (
  `acd_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `acd_user_id` int(10) unsigned NOT NULL,
  `acd_real_name` varbinary(255) NOT NULL DEFAULT '',
  `acd_email` tinyblob NOT NULL,
  `acd_email_authenticated` varbinary(14) DEFAULT NULL,
  `acd_bio` mediumblob NOT NULL,
  `acd_notes` mediumblob NOT NULL,
  `acd_urls` mediumblob NOT NULL,
  `acd_ip` varbinary(255) DEFAULT '',
  `acd_xff` varbinary(255) DEFAULT '',
  `acd_agent` varbinary(255) DEFAULT '',
  `acd_filename` varbinary(255) DEFAULT NULL,
  `acd_storage_key` varbinary(64) DEFAULT NULL,
  `acd_areas` mediumblob NOT NULL,
  `acd_registration` varbinary(14) NOT NULL,
  `acd_accepted` varbinary(14) DEFAULT NULL,
  `acd_user` int(10) unsigned NOT NULL DEFAULT 0,
  `acd_comment` varbinary(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`acd_id`),
  UNIQUE KEY `acd_user_id` (`acd_user_id`,`acd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_account_requests` (
  `acr_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `acr_name` varbinary(255) NOT NULL DEFAULT '',
  `acr_real_name` varbinary(255) NOT NULL DEFAULT '',
  `acr_email` varbinary(255) NOT NULL,
  `acr_email_authenticated` varbinary(14) DEFAULT NULL,
  `acr_email_token` binary(32) DEFAULT NULL,
  `acr_email_token_expires` varbinary(14) DEFAULT NULL,
  `acr_bio` mediumblob NOT NULL,
  `acr_notes` mediumblob NOT NULL,
  `acr_urls` mediumblob NOT NULL,
  `acr_ip` varbinary(255) DEFAULT '',
  `acr_xff` varbinary(255) DEFAULT '',
  `acr_agent` varbinary(255) DEFAULT '',
  `acr_filename` varbinary(255) DEFAULT NULL,
  `acr_storage_key` varbinary(64) DEFAULT NULL,
  `acr_type` tinyint(255) unsigned NOT NULL DEFAULT 0,
  `acr_areas` mediumblob NOT NULL,
  `acr_registration` varbinary(14) NOT NULL,
  `acr_deleted` tinyint(1) NOT NULL,
  `acr_rejected` varbinary(14) DEFAULT NULL,
  `acr_held` varbinary(14) DEFAULT NULL,
  `acr_user` int(10) unsigned NOT NULL DEFAULT 0,
  `acr_comment` varbinary(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`acr_id`),
  UNIQUE KEY `acr_name` (`acr_name`),
  KEY `acr_email` (`acr_email`),
  KEY `acr_email_token` (`acr_email_token`),
  KEY `acr_type_del_reg` (`acr_type`,`acr_deleted`,`acr_registration`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_actor` (
  `actor_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `actor_user` int(10) unsigned DEFAULT NULL,
  `actor_name` varbinary(255) NOT NULL,
  PRIMARY KEY (`actor_id`),
  UNIQUE KEY `actor_name` (`actor_name`),
  UNIQUE KEY `actor_user` (`actor_user`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_archive` (
  `ar_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ar_namespace` int(11) NOT NULL DEFAULT 0,
  `ar_title` varbinary(255) NOT NULL DEFAULT '',
  `ar_comment_id` bigint(20) unsigned NOT NULL,
  `ar_actor` bigint(20) unsigned NOT NULL,
  `ar_timestamp` binary(14) NOT NULL,
  `ar_minor_edit` tinyint(4) NOT NULL DEFAULT 0,
  `ar_rev_id` int(10) unsigned NOT NULL,
  `ar_deleted` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `ar_len` int(10) unsigned DEFAULT NULL,
  `ar_page_id` int(10) unsigned DEFAULT NULL,
  `ar_parent_id` int(10) unsigned DEFAULT NULL,
  `ar_sha1` varbinary(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`ar_id`),
  UNIQUE KEY `ar_revid_uniq` (`ar_rev_id`),
  KEY `ar_name_title_timestamp` (`ar_namespace`,`ar_title`,`ar_timestamp`),
  KEY `ar_actor_timestamp` (`ar_actor`,`ar_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_bot_passwords` (
  `bp_user` int(10) unsigned NOT NULL,
  `bp_app_id` varbinary(32) NOT NULL,
  `bp_password` tinyblob NOT NULL,
  `bp_token` binary(32) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `bp_restrictions` blob NOT NULL,
  `bp_grants` blob NOT NULL,
  PRIMARY KEY (`bp_user`,`bp_app_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_category` (
  `cat_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cat_title` varbinary(255) NOT NULL,
  `cat_pages` int(11) NOT NULL DEFAULT 0,
  `cat_subcats` int(11) NOT NULL DEFAULT 0,
  `cat_files` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`cat_id`),
  UNIQUE KEY `cat_title` (`cat_title`),
  KEY `cat_pages` (`cat_pages`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_categorylinks` (
  `cl_from` int(10) unsigned NOT NULL DEFAULT 0,
  `cl_to` varbinary(255) NOT NULL DEFAULT '',
  `cl_sortkey` varbinary(230) NOT NULL DEFAULT '',
  `cl_sortkey_prefix` varbinary(255) NOT NULL DEFAULT '',
  `cl_timestamp` timestamp NOT NULL,
  `cl_collation` varbinary(32) NOT NULL DEFAULT '',
  `cl_type` enum('page','subcat','file') NOT NULL DEFAULT 'page',
  PRIMARY KEY (`cl_from`,`cl_to`),
  KEY `cl_sortkey` (`cl_to`,`cl_type`,`cl_sortkey`,`cl_from`),
  KEY `cl_timestamp` (`cl_to`,`cl_timestamp`),
  KEY `cl_collation_ext` (`cl_collation`,`cl_to`,`cl_type`,`cl_from`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_change_tag` (
  `ct_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ct_rc_id` int(10) unsigned DEFAULT NULL,
  `ct_log_id` int(10) unsigned DEFAULT NULL,
  `ct_rev_id` int(10) unsigned DEFAULT NULL,
  `ct_params` blob DEFAULT NULL,
  `ct_tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ct_id`),
  UNIQUE KEY `ct_rc_tag_id` (`ct_rc_id`,`ct_tag_id`),
  UNIQUE KEY `ct_log_tag_id` (`ct_log_id`,`ct_tag_id`),
  UNIQUE KEY `ct_rev_tag_id` (`ct_rev_id`,`ct_tag_id`),
  KEY `ct_tag_id_id` (`ct_tag_id`,`ct_rc_id`,`ct_rev_id`,`ct_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_change_tag_def` (
  `ctd_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ctd_name` varbinary(255) NOT NULL,
  `ctd_user_defined` tinyint(1) NOT NULL,
  `ctd_count` bigint(20) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`ctd_id`),
  UNIQUE KEY `ctd_name` (`ctd_name`),
  KEY `ctd_count` (`ctd_count`),
  KEY `ctd_user_defined` (`ctd_user_defined`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_comment` (
  `comment_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_hash` int(11) NOT NULL,
  `comment_text` blob NOT NULL,
  `comment_data` blob DEFAULT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `comment_hash` (`comment_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_content` (
  `content_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `content_size` int(10) unsigned NOT NULL,
  `content_sha1` varbinary(32) NOT NULL,
  `content_model` smallint(5) unsigned NOT NULL,
  `content_address` varbinary(255) NOT NULL,
  PRIMARY KEY (`content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_content_models` (
  `model_id` int(11) NOT NULL AUTO_INCREMENT,
  `model_name` varbinary(64) NOT NULL,
  PRIMARY KEY (`model_id`),
  UNIQUE KEY `model_name` (`model_name`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

INSERT INTO `<<REPLACE_PREFIX>>_content_models` (`model_id`, `model_name`) VALUES
(1,	UNHEX('77696B6974657874'));

CREATE TABLE `<<REPLACE_PREFIX>>_echo_email_batch` (
  `eeb_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `eeb_user_id` int(10) unsigned NOT NULL,
  `eeb_event_priority` tinyint(3) unsigned NOT NULL DEFAULT 10,
  `eeb_event_id` int(10) unsigned NOT NULL,
  `eeb_event_hash` varbinary(32) NOT NULL,
  PRIMARY KEY (`eeb_id`),
  UNIQUE KEY `echo_email_batch_user_event` (`eeb_user_id`,`eeb_event_id`),
  KEY `echo_email_batch_user_hash_priority` (`eeb_user_id`,`eeb_event_hash`,`eeb_event_priority`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_echo_event` (
  `event_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_type` varbinary(64) NOT NULL,
  `event_variant` varbinary(64) DEFAULT NULL,
  `event_agent_id` int(10) unsigned DEFAULT NULL,
  `event_agent_ip` varbinary(39) DEFAULT NULL,
  `event_extra` blob DEFAULT NULL,
  `event_page_id` int(10) unsigned DEFAULT NULL,
  `event_deleted` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`event_id`),
  KEY `echo_event_type` (`event_type`),
  KEY `echo_event_page_id` (`event_page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_echo_notification` (
  `notification_event` int(10) unsigned NOT NULL,
  `notification_user` int(10) unsigned NOT NULL,
  `notification_timestamp` binary(14) NOT NULL,
  `notification_read_timestamp` binary(14) DEFAULT NULL,
  `notification_bundle_hash` varbinary(32) NOT NULL,
  PRIMARY KEY (`notification_user`,`notification_event`),
  KEY `echo_user_timestamp` (`notification_user`,`notification_timestamp`),
  KEY `echo_notification_event` (`notification_event`),
  KEY `echo_notification_user_read_timestamp` (`notification_user`,`notification_read_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_echo_push_provider` (
  `epp_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `epp_name` tinyblob NOT NULL,
  PRIMARY KEY (`epp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_echo_push_subscription` (
  `eps_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `eps_user` int(10) unsigned NOT NULL,
  `eps_token` blob NOT NULL,
  `eps_token_sha256` binary(64) NOT NULL,
  `eps_provider` tinyint(3) unsigned NOT NULL,
  `eps_updated` timestamp NOT NULL,
  `eps_data` blob DEFAULT NULL,
  `eps_topic` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`eps_id`),
  UNIQUE KEY `eps_token_sha256` (`eps_token_sha256`),
  KEY `eps_provider` (`eps_provider`),
  KEY `eps_topic` (`eps_topic`),
  KEY `eps_user` (`eps_user`),
  KEY `eps_token` (`eps_token`(10))
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_echo_push_topic` (
  `ept_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `ept_text` tinyblob NOT NULL,
  PRIMARY KEY (`ept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_echo_target_page` (
  `etp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `etp_page` int(10) unsigned NOT NULL DEFAULT 0,
  `etp_event` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`etp_id`),
  KEY `echo_target_page_event` (`etp_event`),
  KEY `echo_target_page_page_event` (`etp_page`,`etp_event`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_echo_unread_wikis` (
  `euw_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `euw_user` int(10) unsigned NOT NULL,
  `euw_wiki` varbinary(64) NOT NULL,
  `euw_alerts` int(10) unsigned NOT NULL,
  `euw_alerts_ts` binary(14) NOT NULL,
  `euw_messages` int(10) unsigned NOT NULL,
  `euw_messages_ts` binary(14) NOT NULL,
  PRIMARY KEY (`euw_id`),
  UNIQUE KEY `echo_unread_wikis_user_wiki` (`euw_user`,`euw_wiki`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_entityschema_id_counter` (
  `id_value` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_externallinks` (
  `el_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `el_from` int(10) unsigned NOT NULL DEFAULT 0,
  `el_to` blob NOT NULL,
  `el_index` blob NOT NULL,
  `el_index_60` varbinary(60) NOT NULL,
  PRIMARY KEY (`el_id`),
  KEY `el_from` (`el_from`,`el_to`(40)),
  KEY `el_to` (`el_to`(60),`el_from`),
  KEY `el_index` (`el_index`(60)),
  KEY `el_index_60` (`el_index_60`,`el_id`),
  KEY `el_from_index_60` (`el_from`,`el_index_60`,`el_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_filearchive` (
  `fa_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fa_name` varbinary(255) NOT NULL DEFAULT '',
  `fa_archive_name` varbinary(255) DEFAULT '',
  `fa_storage_group` varbinary(16) DEFAULT NULL,
  `fa_storage_key` varbinary(64) DEFAULT '',
  `fa_deleted_user` int(11) DEFAULT NULL,
  `fa_deleted_timestamp` binary(14),
  `fa_deleted_reason_id` bigint(20) unsigned NOT NULL,
  `fa_size` int(10) unsigned DEFAULT 0,
  `fa_width` int(11) DEFAULT 0,
  `fa_height` int(11) DEFAULT 0,
  `fa_metadata` mediumblob DEFAULT NULL,
  `fa_bits` int(11) DEFAULT 0,
  `fa_media_type` enum('UNKNOWN','BITMAP','DRAWING','AUDIO','VIDEO','MULTIMEDIA','OFFICE','TEXT','EXECUTABLE','ARCHIVE','3D') DEFAULT NULL,
  `fa_major_mime` enum('unknown','application','audio','image','text','video','message','model','multipart','chemical') DEFAULT 'unknown',
  `fa_minor_mime` varbinary(100) DEFAULT 'unknown',
  `fa_description_id` bigint(20) unsigned NOT NULL,
  `fa_actor` bigint(20) unsigned NOT NULL,
  `fa_timestamp` binary(14),
  `fa_deleted` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `fa_sha1` varbinary(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`fa_id`),
  KEY `fa_name` (`fa_name`,`fa_timestamp`),
  KEY `fa_storage_group` (`fa_storage_group`,`fa_storage_key`),
  KEY `fa_deleted_timestamp` (`fa_deleted_timestamp`),
  KEY `fa_actor_timestamp` (`fa_actor`,`fa_timestamp`),
  KEY `fa_sha1` (`fa_sha1`(10))
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_image` (
  `img_name` varbinary(255) NOT NULL DEFAULT '',
  `img_size` int(10) unsigned NOT NULL DEFAULT 0,
  `img_width` int(11) NOT NULL DEFAULT 0,
  `img_height` int(11) NOT NULL DEFAULT 0,
  `img_metadata` mediumblob NOT NULL,
  `img_bits` int(11) NOT NULL DEFAULT 0,
  `img_media_type` enum('UNKNOWN','BITMAP','DRAWING','AUDIO','VIDEO','MULTIMEDIA','OFFICE','TEXT','EXECUTABLE','ARCHIVE','3D') DEFAULT NULL,
  `img_major_mime` enum('unknown','application','audio','image','text','video','message','model','multipart','chemical') NOT NULL DEFAULT 'unknown',
  `img_minor_mime` varbinary(100) NOT NULL DEFAULT 'unknown',
  `img_description_id` bigint(20) unsigned NOT NULL,
  `img_actor` bigint(20) unsigned NOT NULL,
  `img_timestamp` binary(14) NOT NULL,
  `img_sha1` varbinary(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`img_name`),
  KEY `img_actor_timestamp` (`img_actor`,`img_timestamp`),
  KEY `img_size` (`img_size`),
  KEY `img_timestamp` (`img_timestamp`),
  KEY `img_sha1` (`img_sha1`(10)),
  KEY `img_media_mime` (`img_media_type`,`img_major_mime`,`img_minor_mime`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_imagelinks` (
  `il_from` int(10) unsigned NOT NULL DEFAULT 0,
  `il_to` varbinary(255) NOT NULL DEFAULT '',
  `il_from_namespace` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`il_from`,`il_to`),
  KEY `il_to` (`il_to`,`il_from`),
  KEY `il_backlinks_namespace` (`il_from_namespace`,`il_to`,`il_from`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_interwiki` (
  `iw_prefix` varbinary(32) NOT NULL,
  `iw_url` blob NOT NULL,
  `iw_api` blob NOT NULL,
  `iw_wikiid` varbinary(64) NOT NULL,
  `iw_local` tinyint(1) NOT NULL,
  `iw_trans` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`iw_prefix`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

INSERT INTO `<<REPLACE_PREFIX>>_interwiki` (`iw_prefix`, `iw_url`, `iw_api`, `iw_wikiid`, `iw_local`, `iw_trans`) VALUES
(UNHEX('6163726F6E796D'),	'https://www.acronymfinder.com/~/search/af.aspx?string=exact&Acronym=$1',	'',	UNHEX(''),	0,	0),
(UNHEX('6164766F6761746F'),	'http://www.advogato.org/$1',	'',	UNHEX(''),	0,	0),
(UNHEX('6172786976'),	'https://www.arxiv.org/abs/$1',	'',	UNHEX(''),	0,	0),
(UNHEX('633266696E64'),	'http://c2.com/cgi/wiki?FindPage&value=$1',	'',	UNHEX(''),	0,	0),
(UNHEX('6361636865'),	'https://www.google.com/search?q=cache:$1',	'',	UNHEX(''),	0,	0),
(UNHEX('636F6D6D6F6E73'),	'https://commons.wikimedia.org/wiki/$1',	'https://commons.wikimedia.org/w/api.php',	UNHEX(''),	0,	0),
(UNHEX('64696374696F6E617279'),	'http://www.dict.org/bin/Dict?Database=*&Form=Dict1&Strategy=*&Query=$1',	'',	UNHEX(''),	0,	0),
(UNHEX('646F69'),	'https://dx.doi.org/$1',	'',	UNHEX(''),	0,	0),
(UNHEX('6472756D636F72707377696B69'),	'http://www.drumcorpswiki.com/$1',	'http://drumcorpswiki.com/api.php',	UNHEX(''),	0,	0),
(UNHEX('64776A77696B69'),	'http://www.suberic.net/cgi-bin/dwj/wiki.cgi?$1',	'',	UNHEX(''),	0,	0),
(UNHEX('656C69627265'),	'http://enciclopedia.us.es/index.php/$1',	'http://enciclopedia.us.es/api.php',	UNHEX(''),	0,	0),
(UNHEX('656D61637377696B69'),	'https://www.emacswiki.org/emacs/$1',	'',	UNHEX(''),	0,	0),
(UNHEX('666F6C646F63'),	'https://foldoc.org/?$1',	'',	UNHEX(''),	0,	0),
(UNHEX('666F7877696B69'),	'https://fox.wikis.com/wc.dll?Wiki~$1',	'',	UNHEX(''),	0,	0),
(UNHEX('667265656273646D616E'),	'https://www.FreeBSD.org/cgi/man.cgi?apropos=1&query=$1',	'',	UNHEX(''),	0,	0),
(UNHEX('67656E746F6F2D77696B69'),	'http://gentoo-wiki.com/$1',	'',	UNHEX(''),	0,	0),
(UNHEX('676F6F676C65'),	'https://www.google.com/search?q=$1',	'',	UNHEX(''),	0,	0),
(UNHEX('676F6F676C6567726F757073'),	'https://groups.google.com/groups?q=$1',	'',	UNHEX(''),	0,	0),
(UNHEX('68616D6D6F6E6477696B69'),	'http://www.dairiki.org/HammondWiki/$1',	'',	UNHEX(''),	0,	0),
(UNHEX('687277696B69'),	'http://www.hrwiki.org/wiki/$1',	'http://www.hrwiki.org/w/api.php',	UNHEX(''),	0,	0),
(UNHEX('696D6462'),	'http://www.imdb.com/find?q=$1&tt=on',	'',	UNHEX(''),	0,	0),
(UNHEX('6B6D77696B69'),	'https://kmwiki.wikispaces.com/$1',	'',	UNHEX(''),	0,	0),
(UNHEX('6C696E757877696B69'),	'http://linuxwiki.de/$1',	'',	UNHEX(''),	0,	0),
(UNHEX('6C6F6A62616E'),	'https://mw.lojban.org/papri/$1',	'',	UNHEX(''),	0,	0),
(UNHEX('6C7177696B69'),	'http://wiki.linuxquestions.org/wiki/$1',	'',	UNHEX(''),	0,	0),
(UNHEX('6D65617462616C6C'),	'http://meatballwiki.org/wiki/$1',	'',	UNHEX(''),	0,	0),
(UNHEX('6D6564696177696B6977696B69'),	'https://www.mediawiki.org/wiki/$1',	'https://www.mediawiki.org/w/api.php',	UNHEX(''),	0,	0),
(UNHEX('6D656D6F7279616C706861'),	'http://en.memory-alpha.org/wiki/$1',	'http://en.memory-alpha.org/api.php',	UNHEX(''),	0,	0),
(UNHEX('6D65746177696B69'),	'http://sunir.org/apps/meta.pl?$1',	'',	UNHEX(''),	0,	0),
(UNHEX('6D65746177696B696D65646961'),	'https://meta.wikimedia.org/wiki/$1',	'https://meta.wikimedia.org/w/api.php',	UNHEX(''),	0,	0),
(UNHEX('6D6F7A696C6C6177696B69'),	'https://wiki.mozilla.org/$1',	'https://wiki.mozilla.org/api.php',	UNHEX(''),	0,	0),
(UNHEX('6D77'),	'https://www.mediawiki.org/wiki/$1',	'https://www.mediawiki.org/w/api.php',	UNHEX(''),	0,	0),
(UNHEX('6F656973'),	'https://oeis.org/$1',	'',	UNHEX(''),	0,	0),
(UNHEX('6F70656E77696B69'),	'http://openwiki.com/ow.asp?$1',	'',	UNHEX(''),	0,	0),
(UNHEX('706D6964'),	'https://www.ncbi.nlm.nih.gov/pubmed/$1?dopt=Abstract',	'',	UNHEX(''),	0,	0),
(UNHEX('707974686F6E696E666F'),	'https://wiki.python.org/moin/$1',	'',	UNHEX(''),	0,	0),
(UNHEX('726663'),	'https://tools.ietf.org/html/rfc$1',	'',	UNHEX(''),	0,	0),
(UNHEX('73323377696B69'),	'http://s23.org/wiki/$1',	'http://s23.org/w/api.php',	UNHEX(''),	0,	0),
(UNHEX('73656174746C65776972656C657373'),	'http://seattlewireless.net/$1',	'',	UNHEX(''),	0,	0),
(UNHEX('73656E736569736C696272617279'),	'https://senseis.xmp.net/?$1',	'',	UNHEX(''),	0,	0),
(UNHEX('73686F757477696B69'),	'http://www.shoutwiki.com/wiki/$1',	'http://www.shoutwiki.com/w/api.php',	UNHEX(''),	0,	0),
(UNHEX('73717565616B'),	'http://wiki.squeak.org/squeak/$1',	'',	UNHEX(''),	0,	0),
(UNHEX('7468656F7065646961'),	'https://www.theopedia.com/$1',	'',	UNHEX(''),	0,	0),
(UNHEX('746D6277'),	'http://www.tmbw.net/wiki/$1',	'http://tmbw.net/wiki/api.php',	UNHEX(''),	0,	0),
(UNHEX('746D6E6574'),	'http://www.technomanifestos.net/?$1',	'',	UNHEX(''),	0,	0),
(UNHEX('7477696B69'),	'http://twiki.org/cgi-bin/view/$1',	'',	UNHEX(''),	0,	0),
(UNHEX('756E6379636C6F7065646961'),	'https://en.uncyclopedia.co/wiki/$1',	'https://en.uncyclopedia.co/w/api.php',	UNHEX(''),	0,	0),
(UNHEX('756E7265616C'),	'https://wiki.beyondunreal.com/$1',	'https://wiki.beyondunreal.com/w/api.php',	UNHEX(''),	0,	0),
(UNHEX('7573656D6F64'),	'http://www.usemod.com/cgi-bin/wiki.pl?$1',	'',	UNHEX(''),	0,	0),
(UNHEX('77696B69'),	'http://c2.com/cgi/wiki?$1',	'',	UNHEX(''),	0,	0),
(UNHEX('77696B6961'),	'http://www.wikia.com/wiki/$1',	'',	UNHEX(''),	0,	0),
(UNHEX('77696B69626F6F6B73'),	'https://en.wikibooks.org/wiki/$1',	'https://en.wikibooks.org/w/api.php',	UNHEX(''),	0,	0),
(UNHEX('77696B6964617461'),	'https://www.wikidata.org/wiki/$1',	'https://www.wikidata.org/w/api.php',	UNHEX(''),	0,	0),
(UNHEX('77696B696631'),	'http://www.wikif1.org/$1',	'',	UNHEX(''),	0,	0),
(UNHEX('77696B69686F77'),	'https://www.wikihow.com/$1',	'https://www.wikihow.com/api.php',	UNHEX(''),	0,	0),
(UNHEX('77696B696D65646961'),	'https://foundation.wikimedia.org/wiki/$1',	'https://foundation.wikimedia.org/w/api.php',	UNHEX(''),	0,	0),
(UNHEX('77696B696E657773'),	'https://en.wikinews.org/wiki/$1',	'https://en.wikinews.org/w/api.php',	UNHEX(''),	0,	0),
(UNHEX('77696B696E666F'),	'http://wikinfo.co/English/index.php/$1',	'',	UNHEX(''),	0,	0),
(UNHEX('77696B697065646961'),	'https://en.wikipedia.org/wiki/$1',	'https://en.wikipedia.org/w/api.php',	UNHEX(''),	0,	0),
(UNHEX('77696B6971756F7465'),	'https://en.wikiquote.org/wiki/$1',	'https://en.wikiquote.org/w/api.php',	UNHEX(''),	0,	0),
(UNHEX('77696B69736F75726365'),	'https://wikisource.org/wiki/$1',	'https://wikisource.org/w/api.php',	UNHEX(''),	0,	0),
(UNHEX('77696B6973706563696573'),	'https://species.wikimedia.org/wiki/$1',	'https://species.wikimedia.org/w/api.php',	UNHEX(''),	0,	0),
(UNHEX('77696B6976657273697479'),	'https://en.wikiversity.org/wiki/$1',	'https://en.wikiversity.org/w/api.php',	UNHEX(''),	0,	0),
(UNHEX('77696B69766F79616765'),	'https://en.wikivoyage.org/wiki/$1',	'https://en.wikivoyage.org/w/api.php',	UNHEX(''),	0,	0),
(UNHEX('77696B74'),	'https://en.wiktionary.org/wiki/$1',	'https://en.wiktionary.org/w/api.php',	UNHEX(''),	0,	0),
(UNHEX('77696B74696F6E617279'),	'https://en.wiktionary.org/wiki/$1',	'https://en.wiktionary.org/w/api.php',	UNHEX(''),	0,	0);

CREATE TABLE `<<REPLACE_PREFIX>>_invitesignup` (
  `is_inviter` int(10) unsigned NOT NULL,
  `is_invitee` int(10) unsigned DEFAULT NULL,
  `is_email` varbinary(255) NOT NULL,
  `is_when` varbinary(14) NOT NULL,
  `is_used` varbinary(14) DEFAULT NULL,
  `is_hash` varbinary(40) NOT NULL,
  `is_groups` mediumblob DEFAULT NULL,
  PRIMARY KEY (`is_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_ipblocks` (
  `ipb_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ipb_address` tinyblob NOT NULL,
  `ipb_user` int(10) unsigned NOT NULL DEFAULT 0,
  `ipb_by_actor` bigint(20) unsigned NOT NULL,
  `ipb_reason_id` bigint(20) unsigned NOT NULL,
  `ipb_timestamp` binary(14) NOT NULL,
  `ipb_auto` tinyint(1) NOT NULL DEFAULT 0,
  `ipb_anon_only` tinyint(1) NOT NULL DEFAULT 0,
  `ipb_create_account` tinyint(1) NOT NULL DEFAULT 1,
  `ipb_enable_autoblock` tinyint(1) NOT NULL DEFAULT 1,
  `ipb_expiry` varbinary(14) NOT NULL,
  `ipb_range_start` tinyblob NOT NULL,
  `ipb_range_end` tinyblob NOT NULL,
  `ipb_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `ipb_block_email` tinyint(1) NOT NULL DEFAULT 0,
  `ipb_allow_usertalk` tinyint(1) NOT NULL DEFAULT 0,
  `ipb_parent_block_id` int(10) unsigned DEFAULT NULL,
  `ipb_sitewide` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`ipb_id`),
  UNIQUE KEY `ipb_address_unique` (`ipb_address`(255),`ipb_user`,`ipb_auto`),
  KEY `ipb_user` (`ipb_user`),
  KEY `ipb_range` (`ipb_range_start`(8),`ipb_range_end`(8)),
  KEY `ipb_timestamp` (`ipb_timestamp`),
  KEY `ipb_expiry` (`ipb_expiry`),
  KEY `ipb_parent_block_id` (`ipb_parent_block_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_ipblocks_restrictions` (
  `ir_ipb_id` int(10) unsigned NOT NULL,
  `ir_type` tinyint(4) NOT NULL,
  `ir_value` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ir_ipb_id`,`ir_type`,`ir_value`),
  KEY `ir_type_value` (`ir_type`,`ir_value`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_ip_changes` (
  `ipc_rev_id` int(10) unsigned NOT NULL DEFAULT 0,
  `ipc_rev_timestamp` binary(14) NOT NULL,
  `ipc_hex` varbinary(35) NOT NULL DEFAULT '',
  PRIMARY KEY (`ipc_rev_id`),
  KEY `ipc_rev_timestamp` (`ipc_rev_timestamp`),
  KEY `ipc_hex_time` (`ipc_hex`,`ipc_rev_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_iwlinks` (
  `iwl_from` int(10) unsigned NOT NULL DEFAULT 0,
  `iwl_prefix` varbinary(32) NOT NULL DEFAULT '',
  `iwl_title` varbinary(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`iwl_from`,`iwl_prefix`,`iwl_title`),
  KEY `iwl_prefix_title_from` (`iwl_prefix`,`iwl_title`,`iwl_from`),
  KEY `iwl_prefix_from_title` (`iwl_prefix`,`iwl_from`,`iwl_title`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_job` (
  `job_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_cmd` varbinary(60) NOT NULL DEFAULT '',
  `job_namespace` int(11) NOT NULL,
  `job_title` varbinary(255) NOT NULL,
  `job_timestamp` binary(14) DEFAULT NULL,
  `job_params` mediumblob NOT NULL,
  `job_random` int(10) unsigned NOT NULL DEFAULT 0,
  `job_attempts` int(10) unsigned NOT NULL DEFAULT 0,
  `job_token` varbinary(32) NOT NULL DEFAULT '',
  `job_token_timestamp` binary(14) DEFAULT NULL,
  `job_sha1` varbinary(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`job_id`),
  KEY `job_sha1` (`job_sha1`),
  KEY `job_cmd_token` (`job_cmd`,`job_token`,`job_random`),
  KEY `job_cmd_token_id` (`job_cmd`,`job_token`,`job_id`),
  KEY `job_cmd` (`job_cmd`,`job_namespace`,`job_title`,`job_params`(128)),
  KEY `job_timestamp` (`job_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_l10n_cache` (
  `lc_lang` varbinary(35) NOT NULL,
  `lc_key` varbinary(255) NOT NULL,
  `lc_value` mediumblob NOT NULL,
  PRIMARY KEY (`lc_lang`,`lc_key`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_langlinks` (
  `ll_from` int(10) unsigned NOT NULL DEFAULT 0,
  `ll_lang` varbinary(35) NOT NULL DEFAULT '',
  `ll_title` varbinary(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ll_from`,`ll_lang`),
  KEY `ll_lang` (`ll_lang`,`ll_title`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_linktarget` (
  `lt_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lt_namespace` int(11) NOT NULL,
  `lt_title` varbinary(255) NOT NULL,
  PRIMARY KEY (`lt_id`),
  UNIQUE KEY `lt_namespace_title` (`lt_namespace`,`lt_title`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_logging` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `log_type` varbinary(32) NOT NULL DEFAULT '',
  `log_action` varbinary(32) NOT NULL DEFAULT '',
  `log_timestamp` binary(14) NOT NULL DEFAULT '19700101000000',
  `log_actor` bigint(20) unsigned NOT NULL,
  `log_namespace` int(11) NOT NULL DEFAULT 0,
  `log_title` varbinary(255) NOT NULL DEFAULT '',
  `log_page` int(10) unsigned DEFAULT NULL,
  `log_comment_id` bigint(20) unsigned NOT NULL,
  `log_params` blob NOT NULL,
  `log_deleted` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`log_id`),
  KEY `log_type_time` (`log_type`,`log_timestamp`),
  KEY `log_actor_time` (`log_actor`,`log_timestamp`),
  KEY `log_page_time` (`log_namespace`,`log_title`,`log_timestamp`),
  KEY `log_times` (`log_timestamp`),
  KEY `log_actor_type_time` (`log_actor`,`log_type`,`log_timestamp`),
  KEY `log_page_id_time` (`log_page`,`log_timestamp`),
  KEY `log_type_action` (`log_type`,`log_action`,`log_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_log_search` (
  `ls_field` varbinary(32) NOT NULL,
  `ls_value` varbinary(255) NOT NULL,
  `ls_log_id` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`ls_field`,`ls_value`,`ls_log_id`),
  KEY `ls_log_id` (`ls_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_mathlatexml` (
  `math_inputhash` varbinary(16) NOT NULL,
  `math_inputtex` blob NOT NULL,
  `math_tex` blob DEFAULT NULL,
  `math_mathml` mediumblob DEFAULT NULL,
  `math_svg` blob DEFAULT NULL,
  `math_style` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`math_inputhash`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_mathoid` (
  `math_inputhash` varbinary(16) NOT NULL,
  `math_input` blob NOT NULL,
  `math_tex` blob DEFAULT NULL,
  `math_mathml` blob DEFAULT NULL,
  `math_svg` blob DEFAULT NULL,
  `math_style` tinyint(4) DEFAULT NULL,
  `math_input_type` tinyint(4) DEFAULT NULL,
  `math_png` mediumblob DEFAULT NULL,
  PRIMARY KEY (`math_inputhash`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_module_deps` (
  `md_module` varbinary(255) NOT NULL,
  `md_skin` varbinary(32) NOT NULL,
  `md_deps` mediumblob NOT NULL,
  PRIMARY KEY (`md_module`,`md_skin`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_oauth2_access_tokens` (
  `oaat_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `oaat_identifier` varbinary(255) NOT NULL,
  `oaat_expires` varbinary(14) NOT NULL,
  `oaat_acceptance_id` int(10) unsigned NOT NULL,
  `oaat_revoked` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`oaat_id`),
  UNIQUE KEY `oaat_identifier` (`oaat_identifier`),
  KEY `oaat_acceptance_id` (`oaat_acceptance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_oauth_accepted_consumer` (
  `oaac_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `oaac_wiki` varbinary(255) NOT NULL,
  `oaac_user_id` int(10) unsigned NOT NULL,
  `oaac_consumer_id` int(10) unsigned NOT NULL,
  `oaac_access_token` varbinary(32) NOT NULL,
  `oaac_access_secret` varbinary(32) NOT NULL,
  `oaac_grants` blob NOT NULL,
  `oaac_accepted` binary(14) NOT NULL,
  `oaac_oauth_version` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`oaac_id`),
  UNIQUE KEY `oaac_access_token` (`oaac_access_token`),
  UNIQUE KEY `oaac_user_consumer_wiki` (`oaac_user_id`,`oaac_consumer_id`,`oaac_wiki`),
  KEY `oaac_consumer_user` (`oaac_consumer_id`,`oaac_user_id`),
  KEY `oaac_user_id` (`oaac_user_id`,`oaac_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_oauth_registered_consumer` (
  `oarc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `oarc_consumer_key` varbinary(32) NOT NULL,
  `oarc_name` varbinary(128) NOT NULL,
  `oarc_user_id` int(10) unsigned NOT NULL,
  `oarc_version` varbinary(32) NOT NULL,
  `oarc_callback_url` blob NOT NULL,
  `oarc_callback_is_prefix` tinyblob DEFAULT NULL,
  `oarc_description` blob NOT NULL,
  `oarc_email` varbinary(255) NOT NULL,
  `oarc_email_authenticated` binary(14) DEFAULT NULL,
  `oarc_developer_agreement` tinyint(4) NOT NULL DEFAULT 0,
  `oarc_owner_only` tinyint(4) NOT NULL DEFAULT 0,
  `oarc_wiki` varbinary(32) NOT NULL,
  `oarc_grants` blob NOT NULL,
  `oarc_registration` binary(14) NOT NULL,
  `oarc_secret_key` varbinary(32) DEFAULT NULL,
  `oarc_rsa_key` blob DEFAULT NULL,
  `oarc_restrictions` blob NOT NULL,
  `oarc_stage` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `oarc_stage_timestamp` binary(14) NOT NULL,
  `oarc_deleted` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `oarc_oauth_version` tinyint(4) NOT NULL DEFAULT 1,
  `oarc_oauth2_allowed_grants` blob DEFAULT NULL,
  `oarc_oauth2_is_confidential` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`oarc_id`),
  UNIQUE KEY `oarc_consumer_key` (`oarc_consumer_key`),
  UNIQUE KEY `oarc_name_version_user` (`oarc_name`,`oarc_user_id`,`oarc_version`),
  KEY `oarc_user_id` (`oarc_user_id`),
  KEY `oarc_stage_timestamp` (`oarc_stage`,`oarc_stage_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_objectcache` (
  `keyname` varbinary(255) NOT NULL DEFAULT '',
  `value` mediumblob DEFAULT NULL,
  `exptime` binary(14) NOT NULL,
  `modtoken` varbinary(17) NOT NULL DEFAULT '00000000000000000',
  `flags` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`keyname`),
  KEY `exptime` (`exptime`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_oldimage` (
  `oi_name` varbinary(255) NOT NULL DEFAULT '',
  `oi_archive_name` varbinary(255) NOT NULL DEFAULT '',
  `oi_size` int(10) unsigned NOT NULL DEFAULT 0,
  `oi_width` int(11) NOT NULL DEFAULT 0,
  `oi_height` int(11) NOT NULL DEFAULT 0,
  `oi_bits` int(11) NOT NULL DEFAULT 0,
  `oi_description_id` bigint(20) unsigned NOT NULL,
  `oi_actor` bigint(20) unsigned NOT NULL,
  `oi_timestamp` binary(14) NOT NULL,
  `oi_metadata` mediumblob NOT NULL,
  `oi_media_type` enum('UNKNOWN','BITMAP','DRAWING','AUDIO','VIDEO','MULTIMEDIA','OFFICE','TEXT','EXECUTABLE','ARCHIVE','3D') DEFAULT NULL,
  `oi_major_mime` enum('unknown','application','audio','image','text','video','message','model','multipart','chemical') NOT NULL DEFAULT 'unknown',
  `oi_minor_mime` varbinary(100) NOT NULL DEFAULT 'unknown',
  `oi_deleted` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `oi_sha1` varbinary(32) NOT NULL DEFAULT '',
  KEY `oi_actor_timestamp` (`oi_actor`,`oi_timestamp`),
  KEY `oi_name_timestamp` (`oi_name`,`oi_timestamp`),
  KEY `oi_name_archive_name` (`oi_name`,`oi_archive_name`(14)),
  KEY `oi_sha1` (`oi_sha1`(10)),
  KEY `oi_timestamp` (`oi_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_page` (
  `page_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_namespace` int(11) NOT NULL,
  `page_title` varbinary(255) NOT NULL,
  `page_is_redirect` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `page_is_new` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `page_random` double unsigned NOT NULL,
  `page_touched` binary(14) NOT NULL,
  `page_links_updated` varbinary(14) DEFAULT NULL,
  `page_latest` int(10) unsigned NOT NULL,
  `page_len` int(10) unsigned NOT NULL,
  `page_content_model` varbinary(32) DEFAULT NULL,
  `page_lang` varbinary(35) DEFAULT NULL,
  PRIMARY KEY (`page_id`),
  UNIQUE KEY `page_name_title` (`page_namespace`,`page_title`),
  KEY `page_random` (`page_random`),
  KEY `page_len` (`page_len`),
  KEY `page_redirect_namespace_len` (`page_is_redirect`,`page_namespace`,`page_len`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_pagelinks` (
  `pl_from` int(10) unsigned NOT NULL DEFAULT 0,
  `pl_namespace` int(11) NOT NULL DEFAULT 0,
  `pl_title` varbinary(255) NOT NULL DEFAULT '',
  `pl_from_namespace` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`pl_from`,`pl_namespace`,`pl_title`),
  KEY `pl_namespace` (`pl_namespace`,`pl_title`,`pl_from`),
  KEY `pl_backlinks_namespace` (`pl_from_namespace`,`pl_namespace`,`pl_title`,`pl_from`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_page_props` (
  `pp_page` int(10) unsigned NOT NULL,
  `pp_propname` varbinary(60) NOT NULL,
  `pp_value` blob NOT NULL,
  `pp_sortkey` float DEFAULT NULL,
  PRIMARY KEY (`pp_page`,`pp_propname`),
  UNIQUE KEY `pp_propname_page` (`pp_propname`,`pp_page`),
  UNIQUE KEY `pp_propname_sortkey_page` (`pp_propname`,`pp_sortkey`,`pp_page`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_page_restrictions` (
  `pr_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pr_page` int(10) unsigned NOT NULL,
  `pr_type` varbinary(60) NOT NULL,
  `pr_level` varbinary(60) NOT NULL,
  `pr_cascade` tinyint(4) NOT NULL,
  `pr_expiry` varbinary(14) DEFAULT NULL,
  PRIMARY KEY (`pr_id`),
  UNIQUE KEY `pr_pagetype` (`pr_page`,`pr_type`),
  KEY `pr_typelevel` (`pr_type`,`pr_level`),
  KEY `pr_level` (`pr_level`),
  KEY `pr_cascade` (`pr_cascade`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_protected_titles` (
  `pt_namespace` int(11) NOT NULL,
  `pt_title` varbinary(255) NOT NULL,
  `pt_user` int(10) unsigned NOT NULL,
  `pt_reason_id` bigint(20) unsigned NOT NULL,
  `pt_timestamp` binary(14) NOT NULL,
  `pt_expiry` varbinary(14) NOT NULL,
  `pt_create_perm` varbinary(60) NOT NULL,
  PRIMARY KEY (`pt_namespace`,`pt_title`),
  KEY `pt_timestamp` (`pt_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_querycache` (
  `qc_type` varbinary(32) NOT NULL,
  `qc_value` int(10) unsigned NOT NULL DEFAULT 0,
  `qc_namespace` int(11) NOT NULL DEFAULT 0,
  `qc_title` varbinary(255) NOT NULL DEFAULT '',
  KEY `qc_type` (`qc_type`,`qc_value`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_querycachetwo` (
  `qcc_type` varbinary(32) NOT NULL,
  `qcc_value` int(10) unsigned NOT NULL DEFAULT 0,
  `qcc_namespace` int(11) NOT NULL DEFAULT 0,
  `qcc_title` varbinary(255) NOT NULL DEFAULT '',
  `qcc_namespacetwo` int(11) NOT NULL DEFAULT 0,
  `qcc_titletwo` varbinary(255) NOT NULL DEFAULT '',
  KEY `qcc_type` (`qcc_type`,`qcc_value`),
  KEY `qcc_title` (`qcc_type`,`qcc_namespace`,`qcc_title`),
  KEY `qcc_titletwo` (`qcc_type`,`qcc_namespacetwo`,`qcc_titletwo`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_querycache_info` (
  `qci_type` varbinary(32) NOT NULL DEFAULT '',
  `qci_timestamp` binary(14) NOT NULL DEFAULT '19700101000000',
  PRIMARY KEY (`qci_type`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_recentchanges` (
  `rc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rc_timestamp` binary(14) NOT NULL,
  `rc_actor` bigint(20) unsigned NOT NULL,
  `rc_namespace` int(11) NOT NULL DEFAULT 0,
  `rc_title` varbinary(255) NOT NULL DEFAULT '',
  `rc_comment_id` bigint(20) unsigned NOT NULL,
  `rc_minor` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `rc_bot` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `rc_new` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `rc_cur_id` int(10) unsigned NOT NULL DEFAULT 0,
  `rc_this_oldid` int(10) unsigned NOT NULL DEFAULT 0,
  `rc_last_oldid` int(10) unsigned NOT NULL DEFAULT 0,
  `rc_type` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `rc_source` varbinary(16) NOT NULL DEFAULT '',
  `rc_patrolled` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `rc_ip` varbinary(40) NOT NULL DEFAULT '',
  `rc_old_len` int(11) DEFAULT NULL,
  `rc_new_len` int(11) DEFAULT NULL,
  `rc_deleted` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `rc_logid` int(10) unsigned NOT NULL DEFAULT 0,
  `rc_log_type` varbinary(255) DEFAULT NULL,
  `rc_log_action` varbinary(255) DEFAULT NULL,
  `rc_params` blob DEFAULT NULL,
  PRIMARY KEY (`rc_id`),
  KEY `rc_timestamp` (`rc_timestamp`),
  KEY `rc_namespace_title_timestamp` (`rc_namespace`,`rc_title`,`rc_timestamp`),
  KEY `rc_cur_id` (`rc_cur_id`),
  KEY `rc_new_name_timestamp` (`rc_new`,`rc_namespace`,`rc_timestamp`),
  KEY `rc_ip` (`rc_ip`),
  KEY `rc_ns_actor` (`rc_namespace`,`rc_actor`),
  KEY `rc_actor` (`rc_actor`,`rc_timestamp`),
  KEY `rc_name_type_patrolled_timestamp` (`rc_namespace`,`rc_type`,`rc_patrolled`,`rc_timestamp`),
  KEY `rc_this_oldid` (`rc_this_oldid`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_redirect` (
  `rd_from` int(10) unsigned NOT NULL DEFAULT 0,
  `rd_namespace` int(11) NOT NULL DEFAULT 0,
  `rd_title` varbinary(255) NOT NULL DEFAULT '',
  `rd_interwiki` varbinary(32) DEFAULT NULL,
  `rd_fragment` varbinary(255) DEFAULT NULL,
  PRIMARY KEY (`rd_from`),
  KEY `rd_ns_title` (`rd_namespace`,`rd_title`,`rd_from`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_revision` (
  `rev_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rev_page` int(10) unsigned NOT NULL,
  `rev_comment_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `rev_actor` bigint(20) unsigned NOT NULL DEFAULT 0,
  `rev_timestamp` binary(14) NOT NULL,
  `rev_minor_edit` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `rev_deleted` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `rev_len` int(10) unsigned DEFAULT NULL,
  `rev_parent_id` int(10) unsigned DEFAULT NULL,
  `rev_sha1` varbinary(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`rev_id`),
  KEY `rev_timestamp` (`rev_timestamp`),
  KEY `rev_page_timestamp` (`rev_page`,`rev_timestamp`),
  KEY `rev_actor_timestamp` (`rev_actor`,`rev_timestamp`,`rev_id`),
  KEY `rev_page_actor_timestamp` (`rev_page`,`rev_actor`,`rev_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_revision_comment_temp` (
  `revcomment_rev` int(10) unsigned NOT NULL,
  `revcomment_comment_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`revcomment_rev`,`revcomment_comment_id`),
  UNIQUE KEY `revcomment_rev` (`revcomment_rev`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_searchindex` (
  `si_page` int(10) unsigned NOT NULL,
  `si_title` varchar(255) NOT NULL DEFAULT '',
  `si_text` mediumtext NOT NULL,
  UNIQUE KEY `si_page` (`si_page`),
  FULLTEXT KEY `si_title` (`si_title`),
  FULLTEXT KEY `si_text` (`si_text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE `<<REPLACE_PREFIX>>_sites` (
  `site_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_global_key` varbinary(64) NOT NULL,
  `site_type` varbinary(32) NOT NULL,
  `site_group` varbinary(32) NOT NULL,
  `site_source` varbinary(32) NOT NULL,
  `site_language` varbinary(35) NOT NULL,
  `site_protocol` varbinary(32) NOT NULL,
  `site_domain` varbinary(255) NOT NULL,
  `site_data` blob NOT NULL,
  `site_forward` tinyint(1) NOT NULL,
  `site_config` blob NOT NULL,
  PRIMARY KEY (`site_id`),
  UNIQUE KEY `site_global_key` (`site_global_key`),
  KEY `site_type` (`site_type`),
  KEY `site_group` (`site_group`),
  KEY `site_source` (`site_source`),
  KEY `site_language` (`site_language`),
  KEY `site_protocol` (`site_protocol`),
  KEY `site_domain` (`site_domain`),
  KEY `site_forward` (`site_forward`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_site_identifiers` (
  `si_type` varbinary(32) NOT NULL,
  `si_key` varbinary(32) NOT NULL,
  `si_site` int(10) unsigned NOT NULL,
  PRIMARY KEY (`si_type`,`si_key`),
  KEY `si_site` (`si_site`),
  KEY `si_key` (`si_key`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_site_stats` (
  `ss_row_id` int(10) unsigned NOT NULL,
  `ss_total_edits` bigint(20) unsigned DEFAULT NULL,
  `ss_good_articles` bigint(20) unsigned DEFAULT NULL,
  `ss_total_pages` bigint(20) unsigned DEFAULT NULL,
  `ss_users` bigint(20) unsigned DEFAULT NULL,
  `ss_active_users` bigint(20) unsigned DEFAULT NULL,
  `ss_images` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`ss_row_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_slots` (
  `slot_revision_id` bigint(20) unsigned NOT NULL,
  `slot_role_id` smallint(5) unsigned NOT NULL,
  `slot_content_id` bigint(20) unsigned NOT NULL,
  `slot_origin` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`slot_revision_id`,`slot_role_id`),
  KEY `slot_revision_origin_role` (`slot_revision_id`,`slot_origin`,`slot_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_slot_roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varbinary(64) NOT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

INSERT INTO `<<REPLACE_PREFIX>>_slot_roles` (`role_id`, `role_name`) VALUES
(1,	UNHEX('6D61696E'));

CREATE TABLE `<<REPLACE_PREFIX>>_templatelinks` (
  `tl_from` int(10) unsigned NOT NULL DEFAULT 0,
  `tl_target_id` bigint(20) unsigned NOT NULL,
  `tl_from_namespace` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`tl_from`,`tl_target_id`),
  KEY `tl_target_id` (`tl_target_id`,`tl_from`),
  KEY `tl_backlinks_namespace_target_id` (`tl_from_namespace`,`tl_target_id`,`tl_from`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_text` (
  `old_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `old_text` mediumblob NOT NULL,
  `old_flags` tinyblob NOT NULL,
  PRIMARY KEY (`old_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_updatelog` (
  `ul_key` varbinary(255) NOT NULL,
  `ul_value` blob DEFAULT NULL,
  PRIMARY KEY (`ul_key`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

INSERT INTO `<<REPLACE_PREFIX>>_updatelog` (`ul_key`, `ul_value`) VALUES
(UNHEX('416464524643616E64504D4944496E74657277696B69'),	NULL),
(UNHEX('436F6E76657274446A76754D65746164617461'),	NULL),
(UNHEX('44656475706C6963617465417263686976655265764964'),	NULL),
(UNHEX('44656C65746544656661756C744D65737361676573'),	NULL),
(UNHEX('46697844656661756C744A736F6E436F6E74656E745061676573'),	NULL),
(UNHEX('4D6967726174654163746F7273'),	NULL),
(UNHEX('4D696772617465436F6D6D656E7473'),	NULL),
(UNHEX('4D6967726174654C696E6B735461626C6574656D706C6174656C696E6B73'),	NULL),
(UNHEX('4D6967726174655265766973696F6E4163746F7254656D70'),	NULL),
(UNHEX('506F70756C6174654368616E6765546167446566'),	NULL),
(UNHEX('506F70756C617465436F6E74656E745461626C6573'),	NULL),
(UNHEX('5265667265736845787465726E616C6C696E6B73496E6465782076312B49444E'),	NULL),
(UNHEX('5570646174654563686F536368656D61466F725375707072657373696F6E'),	NULL),
(UNHEX('57696B69626173652D436C69656E742D7072696D65556E6578706563746564556E636F6E6E6563746564506167652D7633'),	NULL),
(UNHEX('57696B69626173655C5265706F5C53746F72655C53716C5C4461746162617365536368656D61557064617465723A3A72656275696C6450726F70657274795465726D73'),	NULL),
(UNHEX('6163636F756E745F72657175657374732D6163725F656D61696C2D2F7661722F7777772F68746D6C2F772F657874656E73696F6E732F436F6E6669726D4163636F756E742F696E636C756465732F6261636B656E642F736368656D612F6D7973716C2F70617463682D6163725F656D61696C2D766172636861722E73716C'),	NULL),
(UNHEX('6163746F722D6163746F725F6E616D652D70617463682D6163746F722D6163746F725F6E616D652D76617262696E6172792E73716C'),	NULL),
(UNHEX('617263686976652D61725F74696D657374616D702D64726F7044656661756C74'),	NULL),
(UNHEX('617263686976652D61725F7469746C652D70617463682D617263686976652D61725F7469746C652D76617262696E6172792E73716C'),	NULL),
(UNHEX('63617465676F72792D6361745F7469746C652D70617463682D63617465676F72792D6361745F7469746C652D76617262696E6172792E73716C'),	NULL),
(UNHEX('63617465676F72796C696E6B732D636C5F746F2D70617463682D63617465676F72796C696E6B732D636C5F746F2D76617262696E6172792E73716C'),	NULL),
(UNHEX('636C65616E757020656D7074792063617465676F72696573'),	NULL),
(UNHEX('636F6E74656E745F6D6F64656C732D6D6F64656C5F69642D70617463682D636F6E74656E745F6D6F64656C732D6D6F64656C5F69642E73716C'),	NULL),
(UNHEX('6563686F5F756E726561645F77696B69732D6575775F77696B692D2F7661722F7777772F68746D6C2F772F657874656E73696F6E732F4563686F2F73716C2F6D7973716C2F70617463682D696E6372656173652D766172636861722D6563686F5F756E726561645F77696B69732D6575775F77696B692E73716C'),	NULL),
(UNHEX('65787465726E616C6C696E6B732D656C5F696E6465785F36302D64726F7044656661756C74'),	NULL),
(UNHEX('66696C65617263686976652D66615F64656C657465645F74696D657374616D702D64726F7044656661756C74'),	NULL),
(UNHEX('66696C65617263686976652D66615F69642D70617463682D66696C65617263686976652D66615F69642E73716C'),	NULL),
(UNHEX('66696C65617263686976652D66615F6D616A6F725F6D696D652D70617463682D66615F6D616A6F725F6D696D652D6368656D6963616C2E73716C'),	NULL),
(UNHEX('66696C65617263686976652D66615F6E616D652D70617463682D66696C65617263686976652D66615F6E616D652E73716C'),	NULL),
(UNHEX('66696C65617263686976652D66615F74696D657374616D702D64726F7044656661756C74'),	NULL),
(UNHEX('6669782070726F746F636F6C2D72656C61746976652055524C7320696E2065787465726E616C6C696E6B73'),	NULL),
(UNHEX('696D6167652D696D675F6D616A6F725F6D696D652D70617463682D696D6167652D696D675F6D616A6F725F6D696D652D64656661756C742E73716C'),	NULL),
(UNHEX('696D6167652D696D675F6D616A6F725F6D696D652D70617463682D696D675F6D616A6F725F6D696D652D6368656D6963616C2E73716C'),	NULL),
(UNHEX('696D6167652D696D675F6E616D652D70617463682D696D6167652D696D675F6E616D652D76617262696E6172792E73716C'),	NULL),
(UNHEX('696D6167652D696D675F74696D657374616D702D64726F7044656661756C74'),	NULL),
(UNHEX('696D6167652D696D675F74696D657374616D702D70617463682D696D6167652D696D675F74696D657374616D702E73716C'),	NULL),
(UNHEX('696D6167656C696E6B732D696C5F746F2D70617463682D696D6167656C696E6B732D696C5F746F2D76617262696E6172792E73716C'),	NULL),
(UNHEX('69705F6368616E6765732D6970635F7265765F74696D657374616D702D64726F7044656661756C74'),	NULL),
(UNHEX('6970626C6F636B732D6970625F6578706972792D64726F7044656661756C74'),	NULL),
(UNHEX('6970626C6F636B732D6970625F69642D70617463682D6970626C6F636B732D6970625F69642E73716C'),	NULL),
(UNHEX('6970626C6F636B732D6970625F74696D657374616D702D64726F7044656661756C74'),	NULL),
(UNHEX('6970626C6F636B735F7265737472696374696F6E732D69725F6970625F69642D70617463682D6970626C6F636B735F7265737472696374696F6E732D69725F6970625F69642E73716C'),	NULL),
(UNHEX('6970626C6F636B735F7265737472696374696F6E732D69725F747970652D70617463682D6970626C6F636B735F7265737472696374696F6E732D69725F747970652E73716C'),	NULL),
(UNHEX('6970626C6F636B735F7265737472696374696F6E732D69725F76616C75652D70617463682D6970626C6F636B735F7265737472696374696F6E732D69725F76616C75652E73716C'),	NULL),
(UNHEX('69776C696E6B732D69776C5F7072656669782D70617463682D657874656E642D69776C696E6B732D69776C5F7072656669782E73716C'),	NULL),
(UNHEX('69776C696E6B732D69776C5F7469746C652D70617463682D69776C696E6B732D69776C5F7469746C652D76617262696E6172792E73716C'),	NULL),
(UNHEX('6A6F622D6A6F625F74696D657374616D702D70617463682D6A6F625F6A6F625F74696D657374616D702E73716C'),	NULL),
(UNHEX('6A6F622D6A6F625F7469746C652D70617463682D6A6F622D6A6F625F7469746C652D76617262696E6172792E73716C'),	NULL),
(UNHEX('6A6F622D6A6F625F746F6B656E5F74696D657374616D702D70617463682D6A6F625F6A6F625F746F6B656E5F74696D657374616D702E73716C'),	NULL),
(UNHEX('6A6F622D70617463682D6A6F622D706172616D732D6D656469756D626C6F622E73716C'),	NULL),
(UNHEX('6C616E676C696E6B732D6C6C5F7469746C652D70617463682D6C616E676C696E6B732D6C6C5F7469746C652D76617262696E6172792E73716C'),	NULL),
(UNHEX('6C6F6767696E672D6C6F675F7469746C652D70617463682D6C6F6767696E672D6C6F675F7469746C652D76617262696E6172792E73716C'),	NULL),
(UNHEX('6F617574685F61636365707465645F636F6E73756D65722D6F6161635F61636365707465642D2F7661722F7777772F68746D6C2F772F657874656E73696F6E732F4F417574682F736368656D612F6D7973716C2F70617463682D6F617574685F61636365707465645F636F6E73756D65722D74696D657374616D702E73716C'),	NULL),
(UNHEX('6F617574685F726567697374657265645F636F6E73756D65722D6F6172635F656D61696C5F61757468656E746963617465642D2F7661722F7777772F68746D6C2F772F657874656E73696F6E732F4F417574682F736368656D612F6D7973716C2F70617463682D6F617574685F726567697374657265645F636F6E73756D65722D74696D657374616D702E73716C'),	NULL),
(UNHEX('6F626A65637463616368652D65787074696D652D70617463682D6F626A65637463616368652D65787074696D652D6E6F746E756C6C2E73716C'),	NULL),
(UNHEX('6F6C64696D6167652D6F695F6D616A6F725F6D696D652D70617463682D6F695F6D616A6F725F6D696D652D6368656D6963616C2E73716C'),	NULL),
(UNHEX('6F6C64696D6167652D6F695F6E616D652D70617463682D6F6C64696D6167652D6F695F6E616D652D76617262696E6172792E73716C'),	NULL),
(UNHEX('6F6C64696D6167652D6F695F74696D657374616D702D64726F7044656661756C74'),	NULL),
(UNHEX('706167652D706167655F7469746C652D70617463682D706167652D706167655F7469746C652D76617262696E6172792E73716C'),	NULL),
(UNHEX('706167652D706167655F746F75636865642D64726F7044656661756C74'),	NULL),
(UNHEX('706167655F70726F70732D70705F706167652D70617463682D706167655F70726F70732D70705F706167652E73716C'),	NULL),
(UNHEX('706167655F7265737472696374696F6E732D70725F706167652D70617463682D706167655F7265737472696374696F6E732D70725F706167652E73716C'),	NULL),
(UNHEX('706167656C696E6B732D706C5F7469746C652D70617463682D706167656C696E6B732D706C5F7469746C652D76617262696E6172792E73716C'),	NULL),
(UNHEX('706F70756C617465202A5F66726F6D5F6E616D657370616365'),	NULL),
(UNHEX('706F70756C6174652065787465726E616C6C696E6B732E656C5F696E6465785F3630'),	NULL),
(UNHEX('706F70756C6174652066615F73686131'),	NULL),
(UNHEX('706F70756C61746520696D675F73686131'),	NULL),
(UNHEX('706F70756C6174652069705F6368616E676573'),	NULL),
(UNHEX('706F70756C6174652070705F736F72746B6579'),	NULL),
(UNHEX('706F70756C617465207265765F6C656E20616E642061725F6C656E'),	NULL),
(UNHEX('706F70756C617465207265765F73686131'),	NULL),
(UNHEX('70726F7465637465645F7469746C65732D70745F6578706972792D64726F7044656661756C74'),	NULL),
(UNHEX('70726F7465637465645F7469746C65732D70745F7469746C652D70617463682D70726F7465637465645F7469746C65732D70745F7469746C652D76617262696E6172792E73716C'),	NULL),
(UNHEX('717565727963616368652D71635F7469746C652D70617463682D717565727963616368652D71635F7469746C652D76617262696E6172792E73716C'),	NULL),
(UNHEX('7175657279636163686574776F2D7163635F7469746C652D70617463682D7175657279636163686574776F2D7163635F7469746C652D76617262696E6172792E73716C'),	NULL),
(UNHEX('726563656E746368616E6765732D72635F69642D70617463682D726563656E746368616E6765732D72635F69642E73716C'),	NULL),
(UNHEX('726563656E746368616E6765732D72635F74696D657374616D702D64726F7044656661756C74'),	NULL),
(UNHEX('726563656E746368616E6765732D72635F74696D657374616D702D70617463682D726563656E746368616E6765732D72635F74696D657374616D702E73716C'),	NULL),
(UNHEX('726563656E746368616E6765732D72635F7469746C652D70617463682D726563656E746368616E6765732D72635F7469746C652D76617262696E6172792E73716C'),	NULL),
(UNHEX('72656469726563742D72645F7469746C652D70617463682D72656469726563742D72645F7469746C652D76617262696E6172792E73716C'),	NULL),
(UNHEX('7265766973696F6E2D7265765F74696D657374616D702D64726F7044656661756C74'),	NULL),
(UNHEX('736974655F73746174732D70617463682D736974655F73746174732D6D6F646966792E73716C'),	NULL),
(UNHEX('73697465732D736974655F676C6F62616C5F6B65792D70617463682D73697465732D736974655F676C6F62616C5F6B65792E73716C'),	NULL),
(UNHEX('736C6F745F726F6C65732D726F6C655F69642D70617463682D736C6F745F726F6C65732D726F6C655F69642E73716C'),	NULL),
(UNHEX('75706C6F616473746173682D75735F74696D657374616D702D70617463682D75706C6F616473746173682D75735F74696D657374616D702E73716C'),	NULL),
(UNHEX('757365722D757365725F65646974636F756E742D70617463682D757365722D757365725F65646974636F756E742E73716C'),	NULL),
(UNHEX('757365722D757365725F6E616D652D70617463682D757365725F7461626C652D757064617465732E73716C'),	NULL),
(UNHEX('757365725F666F726D65725F67726F7570732D7566675F67726F75702D70617463682D7566675F67726F75702D6C656E6774682D696E6372656173652D3235352E73716C'),	NULL),
(UNHEX('757365725F67726F7570732D75675F67726F75702D70617463682D75675F67726F75702D6C656E6774682D696E6372656173652D3235352E73716C'),	NULL),
(UNHEX('757365725F6E657774616C6B2D757365725F6C6173745F74696D657374616D702D70617463682D757365725F6E657774616C6B2D757365725F6C6173745F74696D657374616D702D62696E6172792E73716C'),	NULL),
(UNHEX('757365725F70726F706572746965732D75705F70726F70657274792D70617463682D75705F70726F70657274792E73716C'),	NULL),
(UNHEX('77617463686C6973742D776C5F6E6F74696669636174696F6E74696D657374616D702D70617463682D77617463686C6973742D776C5F6E6F74696669636174696F6E74696D657374616D702E73716C'),	NULL),
(UNHEX('77617463686C6973742D776C5F7469746C652D70617463682D77617463686C6973742D776C5F7469746C652D76617262696E6172792E73716C'),	NULL),
(UNHEX('77625F6368616E6765732D6368616E67655F696E666F2D2F7661722F7777772F68746D6C2F772F657874656E73696F6E732F57696B69626173652F7265706F2F696E636C756465732F53746F72652F53716C2F2E2E2F2E2E2F2E2E2F73716C2F6D7973716C2F61726368697665732F4D616B654368616E6765496E666F4C61726765722E73716C'),	NULL),
(UNHEX('77625F6368616E6765732D6368616E67655F74696D652D2F7661722F7777772F68746D6C2F772F657874656E73696F6E732F57696B69626173652F7265706F2F696E636C756465732F53746F72652F53716C2F2E2E2F2E2E2F2E2E2F73716C2F6D7973716C2F61726368697665732F70617463682D77625F6368616E6765732D6368616E67655F74696D657374616D702E73716C'),	NULL),
(UNHEX('77625F6974656D735F7065725F736974652D6970735F736974655F706167652D2F7661722F7777772F68746D6C2F772F657874656E73696F6E732F57696B69626173652F7265706F2F696E636C756465732F53746F72652F53716C2F2E2E2F2E2E2F2E2E2F73716C2F6D7973716C2F61726368697665732F4D616B6549707353697465506167654C61726765722E73716C'),	NULL);

CREATE TABLE `<<REPLACE_PREFIX>>_uploadstash` (
  `us_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `us_user` int(10) unsigned NOT NULL,
  `us_key` varbinary(255) NOT NULL,
  `us_orig_path` varbinary(255) NOT NULL,
  `us_path` varbinary(255) NOT NULL,
  `us_source_type` varbinary(50) DEFAULT NULL,
  `us_timestamp` binary(14) NOT NULL,
  `us_status` varbinary(50) NOT NULL,
  `us_chunk_inx` int(10) unsigned DEFAULT NULL,
  `us_props` blob DEFAULT NULL,
  `us_size` int(10) unsigned NOT NULL,
  `us_sha1` varbinary(31) NOT NULL,
  `us_mime` varbinary(255) DEFAULT NULL,
  `us_media_type` enum('UNKNOWN','BITMAP','DRAWING','AUDIO','VIDEO','MULTIMEDIA','OFFICE','TEXT','EXECUTABLE','ARCHIVE','3D') DEFAULT NULL,
  `us_image_width` int(10) unsigned DEFAULT NULL,
  `us_image_height` int(10) unsigned DEFAULT NULL,
  `us_image_bits` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`us_id`),
  UNIQUE KEY `us_key` (`us_key`),
  KEY `us_user` (`us_user`),
  KEY `us_timestamp` (`us_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varbinary(255) NOT NULL DEFAULT '',
  `user_real_name` varbinary(255) NOT NULL DEFAULT '',
  `user_password` tinyblob NOT NULL,
  `user_newpassword` tinyblob NOT NULL,
  `user_newpass_time` binary(14) DEFAULT NULL,
  `user_email` tinyblob NOT NULL,
  `user_touched` binary(14) NOT NULL,
  `user_token` binary(32) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `user_email_authenticated` binary(14) DEFAULT NULL,
  `user_email_token` binary(32) DEFAULT NULL,
  `user_email_token_expires` binary(14) DEFAULT NULL,
  `user_registration` binary(14) DEFAULT NULL,
  `user_editcount` int(10) unsigned DEFAULT NULL,
  `user_password_expires` varbinary(14) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_name` (`user_name`),
  KEY `user_email_token` (`user_email_token`),
  KEY `user_email` (`user_email`(50))
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_user_autocreate_serial` (
  `uas_shard` int(10) unsigned NOT NULL,
  `uas_value` int(10) unsigned NOT NULL,
  PRIMARY KEY (`uas_shard`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_user_former_groups` (
  `ufg_user` int(10) unsigned NOT NULL DEFAULT 0,
  `ufg_group` varbinary(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ufg_user`,`ufg_group`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_user_groups` (
  `ug_user` int(10) unsigned NOT NULL DEFAULT 0,
  `ug_group` varbinary(255) NOT NULL DEFAULT '',
  `ug_expiry` varbinary(14) DEFAULT NULL,
  PRIMARY KEY (`ug_user`,`ug_group`),
  KEY `ug_group` (`ug_group`),
  KEY `ug_expiry` (`ug_expiry`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_user_newtalk` (
  `user_id` int(10) unsigned NOT NULL DEFAULT 0,
  `user_ip` varbinary(40) NOT NULL DEFAULT '',
  `user_last_timestamp` binary(14) DEFAULT NULL,
  KEY `un_user_id` (`user_id`),
  KEY `un_user_ip` (`user_ip`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_user_properties` (
  `up_user` int(10) unsigned NOT NULL,
  `up_property` varbinary(255) NOT NULL,
  `up_value` blob DEFAULT NULL,
  PRIMARY KEY (`up_user`,`up_property`),
  KEY `up_property` (`up_property`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_watchlist` (
  `wl_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `wl_user` int(10) unsigned NOT NULL,
  `wl_namespace` int(11) NOT NULL DEFAULT 0,
  `wl_title` varbinary(255) NOT NULL DEFAULT '',
  `wl_notificationtimestamp` binary(14) DEFAULT NULL,
  PRIMARY KEY (`wl_id`),
  UNIQUE KEY `wl_user` (`wl_user`,`wl_namespace`,`wl_title`),
  KEY `wl_namespace_title` (`wl_namespace`,`wl_title`),
  KEY `wl_user_notificationtimestamp` (`wl_user`,`wl_notificationtimestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_watchlist_expiry` (
  `we_item` int(10) unsigned NOT NULL,
  `we_expiry` binary(14) NOT NULL,
  PRIMARY KEY (`we_item`),
  KEY `we_expiry` (`we_expiry`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_wbc_entity_usage` (
  `eu_row_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `eu_entity_id` varbinary(255) NOT NULL,
  `eu_aspect` varbinary(37) NOT NULL,
  `eu_page_id` int(11) NOT NULL,
  PRIMARY KEY (`eu_row_id`),
  UNIQUE KEY `eu_entity_id` (`eu_entity_id`,`eu_aspect`,`eu_page_id`),
  KEY `eu_page_id` (`eu_page_id`,`eu_entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_wbt_item_terms` (
  `wbit_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `wbit_item_id` int(10) unsigned NOT NULL,
  `wbit_term_in_lang_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`wbit_id`),
  UNIQUE KEY `wbt_item_terms_term_in_lang_id_item_id` (`wbit_term_in_lang_id`,`wbit_item_id`),
  KEY `wbt_item_terms_item_id` (`wbit_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_wbt_property_terms` (
  `wbpt_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `wbpt_property_id` int(10) unsigned NOT NULL,
  `wbpt_term_in_lang_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`wbpt_id`),
  UNIQUE KEY `wbt_property_terms_term_in_lang_id_property_id` (`wbpt_term_in_lang_id`,`wbpt_property_id`),
  KEY `wbt_property_terms_property_id` (`wbpt_property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_wbt_term_in_lang` (
  `wbtl_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `wbtl_type_id` int(10) unsigned NOT NULL,
  `wbtl_text_in_lang_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`wbtl_id`),
  UNIQUE KEY `wbt_term_in_lang_text_in_lang_id_lang_id` (`wbtl_text_in_lang_id`,`wbtl_type_id`),
  KEY `wbt_term_in_lang_type_id_text_in` (`wbtl_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_wbt_text` (
  `wbx_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `wbx_text` varbinary(255) NOT NULL,
  PRIMARY KEY (`wbx_id`),
  UNIQUE KEY `wbt_text_text` (`wbx_text`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_wbt_text_in_lang` (
  `wbxl_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `wbxl_language` varbinary(20) NOT NULL,
  `wbxl_text_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`wbxl_id`),
  UNIQUE KEY `wbt_text_in_lang_text_id_text_id` (`wbxl_text_id`,`wbxl_language`),
  KEY `wbt_text_in_lang_language` (`wbxl_language`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_wbt_type` (
  `wby_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `wby_name` varbinary(45) NOT NULL,
  PRIMARY KEY (`wby_id`),
  UNIQUE KEY `wbt_type_name` (`wby_name`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_wb_changes` (
  `change_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `change_type` varbinary(25) NOT NULL,
  `change_time` binary(14) NOT NULL,
  `change_object_id` varbinary(14) NOT NULL,
  `change_revision_id` int(10) unsigned NOT NULL,
  `change_user_id` int(10) unsigned NOT NULL,
  `change_info` mediumblob NOT NULL,
  PRIMARY KEY (`change_id`),
  KEY `wb_changes_change_time` (`change_time`),
  KEY `wb_changes_change_revision_id` (`change_revision_id`),
  KEY `change_object_id` (`change_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_wb_changes_subscription` (
  `cs_row_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cs_entity_id` varbinary(255) NOT NULL,
  `cs_subscriber_id` varbinary(255) NOT NULL,
  PRIMARY KEY (`cs_row_id`),
  UNIQUE KEY `cs_entity_id` (`cs_entity_id`,`cs_subscriber_id`),
  KEY `cs_subscriber_id` (`cs_subscriber_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_wb_id_counters` (
  `id_value` int(10) unsigned NOT NULL,
  `id_type` varbinary(32) NOT NULL,
  UNIQUE KEY `wb_id_counters_type` (`id_type`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_wb_items_per_site` (
  `ips_row_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ips_item_id` int(10) unsigned NOT NULL,
  `ips_site_id` varbinary(32) NOT NULL,
  `ips_site_page` varbinary(310) NOT NULL,
  PRIMARY KEY (`ips_row_id`),
  UNIQUE KEY `wb_ips_item_site_page` (`ips_site_id`,`ips_site_page`),
  KEY `wb_ips_item_id` (`ips_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

CREATE TABLE `<<REPLACE_PREFIX>>_wb_property_info` (
  `pi_property_id` int(10) unsigned NOT NULL,
  `pi_type` varbinary(32) NOT NULL,
  `pi_info` blob NOT NULL,
  PRIMARY KEY (`pi_property_id`),
  KEY `pi_type` (`pi_type`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;
-- 2023-03-23 17:41:40
