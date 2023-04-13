-- This file is automatically generated using maintenance/generateSchemaChangeSql.php.
-- Source: maintenance/abstractSchemaChanges/patch-user-user_editcount.json
-- Do not modify this file directly.
-- See https://www.mediawiki.org/wiki/Manual:Schema_changes
DROP  INDEX user_name;
DROP  INDEX user_email_token;
DROP  INDEX user_email;
CREATE TEMPORARY TABLE /*_*/__temp__user AS
SELECT  user_id,  user_name,  user_real_name,  user_password,  user_newpassword,  user_newpass_time,  user_email,  user_touched,  user_token,  user_email_authenticated,  user_email_token,  user_email_token_expires,  user_registration,  user_editcount,  user_password_expires
FROM  /*_*/user;
DROP  TABLE  /*_*/user;
CREATE TABLE  /*_*/user (    user_id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,    user_name BLOB DEFAULT '' NOT NULL,    user_real_name BLOB DEFAULT '' NOT NULL,    user_password BLOB NOT NULL, user_newpassword BLOB NOT NULL,    user_newpass_time BLOB DEFAULT NULL,    user_email BLOB NOT NULL, user_touched BLOB NOT NULL,    user_token BLOB DEFAULT '' NOT NULL,    user_email_authenticated BLOB DEFAULT NULL,    user_email_token BLOB DEFAULT NULL,    user_email_token_expires BLOB DEFAULT NULL,    user_registration BLOB DEFAULT NULL,    user_editcount INTEGER UNSIGNED DEFAULT NULL,    user_password_expires BLOB DEFAULT NULL  );
INSERT INTO  /*_*/user (    user_id, user_name, user_real_name,    user_password, user_newpassword,    user_newpass_time, user_email, user_touched,    user_token, user_email_authenticated,    user_email_token, user_email_token_expires,    user_registration, user_editcount,    user_password_expires  )
SELECT  user_id,  user_name,  user_real_name,  user_password,  user_newpassword,  user_newpass_time,  user_email,  user_touched,  user_token,  user_email_authenticated,  user_email_token,  user_email_token_expires,  user_registration,  user_editcount,  user_password_expires
FROM  /*_*/__temp__user;
DROP  TABLE /*_*/__temp__user;
CREATE UNIQUE INDEX user_name ON  /*_*/user (user_name);
CREATE INDEX user_email_token ON  /*_*/user (user_email_token);
CREATE INDEX user_email ON  /*_*/user (user_email);