-- Invite signup signups
CREATE TABLE /*$wgDBprefix*/invitesignup (
  is_inviter INTEGER  NOT NULL,
  is_invitee INTEGER,
  is_email   BYTEA    NOT NULL,
  is_when    BYTEA    NOT NULL,
  is_used    BYTEA,
  is_hash    BYTEA    NOT NULL,
  is_groups  BYTEA,

  PRIMARY KEY (is_hash)
) /*$wgDBTableOptions*/;
