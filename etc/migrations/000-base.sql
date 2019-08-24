pragma journal_mode=wal;

begin transaction;

  create table if not exists db_meta (key string primary key, value);
  insert or replace into db_meta values ('schema_version', 0);

  create table users (
    id integer primary key,
    username string not null,
    password string not null
  );
  create unique index users_username on users (username);

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
    title string not null,
    base_url string not null,
    target_path string not null
  );

  create table templates (
    id integer primary key,
    site_id integer not null,
    type string, -- 'index'/'entry'/null
    name string not null,
    dependencies string, -- JSON array
    modified_at integer,
    content string
  );
  create unique index templates_name on templates (site_id, name);

  create table posts (
    id integer primary key,
    site_id integer not null,
    author_id integer not null,
    state string not null, -- 'draft'/'published'
    slug string not null,
    title string not null,
    published_at integer not null,
    modified_at integer,
    content_type string not null,
    content string not null,
    foreign key (site_id) references sites (id),
    foreign key (author_id) references users (id)
  );
  create index posts_published_at on posts (published_at);


commit;
