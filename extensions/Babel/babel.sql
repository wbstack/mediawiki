CREATE TABLE /*_*/babel (
	-- user id
	babel_user int UNSIGNED NOT NULL,
	-- language code
	babel_lang varbinary(35) NOT NULL,
	-- level (1-5, N)
	babel_level varbinary(2) NOT NULL,

	PRIMARY KEY ( babel_user, babel_lang )
) /*$wgDBTableOptions*/;

-- Query all users who know a language at a specific level
CREATE INDEX /*i*/babel_lang_level ON /*_*/babel (babel_lang, babel_level);
