pragma journal_mode=wal;

begin transaction;

  create table if not exists db_meta (key string primary key, value);
  insert or replace into db_meta values ('schema_version', 0);

  create table site_meta (key string primary key, value string);

  create table posts (
    id integer primary key,
    author_id integer not null,
    state string not null, -- 'draft' or 'published'
    slug string not null,
    title string not null,
    published_at integer not null,
    modified_at integer,
    content string not null,
    content_type string not null,
    content_rendered string
  );
  create index posts_idx_slug on posts (slug);
  create index posts_idx_published_at on posts (published_at);

commit;
