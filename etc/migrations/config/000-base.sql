pragma journal_mode=wal;

begin transaction;

  create table if not exists db_meta (key string primary key, value);
  insert or replace into db_meta values ('schema_version', 0);

  create table config_values (key string primary key, value string);

  create table users (
    id integer primary key,
    username string not null,
    password string not null
  );
  create unique index users_idx_username on users (username);

  create table login_tokens (
    lookup string primary key,
    secret string not null,
    user_id integer not null,
    created_at integer not null,
    valid_until integer not null,
    foreign key (user_id) references users (id)
  );

  create table sites (
    id integer primary key,
    name string not null,
    db_location string not null
  );

commit;
