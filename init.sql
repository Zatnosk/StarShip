CREATE DATABASE StarShip;
CREATE USER 'starship'@'localhost' IDENTIFIED BY 'your password';
GRANT INSERT,SELECT,UPDATE,DELETE ON *.StarShip TO 'starship'@'localhost';
FLUSH PRIVILEGES;

USE StarShip;

-- base account stuff
CREATE TABLE accounts (
  uid char(24) not null primary key,  -- different from public-facing IDs for security reasons only
  actor varchar(255),                 -- unique actor ID, figure out a good actual size for this
  account_name varchar() not null,    -- actor account name, represented in the webfinger etc.
  name varchar(32),                   -- display name
  email varchar(32),
  hash char(255)
);

CREATE TABLE remote_accounts (
  actor varchar() not null primary key,     -- figure out a good actual size for this
  account_name varchar() not null,
  name varchar(32),                         -- display name
  inbox varchar(255),                       -- this one too
);

-- all active/approved Followers for local accounts
CREATE TABLE followers (
  uid char(24) not null,          -- local account's UUID
  actor varchar() not null,       -- remote-or-local follower actor ID
  inbox varchar() not null,       -- remote-or-local inbox to deliver to
  unlocked boolean default false, -- whether or not they can view "locked" objects
  local boolean default false,    -- whether the actor is local, for convenience
  PRIMARY KEY (uid,actor)
);

-- all accounts that local accounts are Following
CREATE TABLE followers (
  uid char(24) not null,          -- local account's UUID
  actor varchar() not null,       -- remote-or-local followed actor ID
  unlocked boolean default false, -- whether or not they can view "locked" objects
  local boolean default false,    -- whether the actor is local, for convenience
  PRIMARY KEY (uid,actor)
);

CREATE TABLE activities (
  id varchar() not null primary key,
  actor varchar() not null,
  recieved date not null,
  updated date,
  typ int not null
);

CREATE TABLE objects (
  id varchar() not null primary key,
  attribution varchar() not null,
  typ int not null,
  published date not null,
  updated date,
  content text,
  source text
);

