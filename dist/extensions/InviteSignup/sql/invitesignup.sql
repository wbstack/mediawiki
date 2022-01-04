-- Invite signup signups
CREATE TABLE /*$wgDBprefix*/invitesignup (
  is_inviter int unsigned NOT NULL,
  is_invitee int unsigned,
  is_email varchar(255) binary NOT NULL,
  is_when varbinary(14) NOT NULL,
  is_used varbinary(14),
  is_hash varbinary(40) NOT NULL,
  is_groups mediumblob,

  PRIMARY KEY (is_hash)
) /*$wgDBTableOptions*/;
