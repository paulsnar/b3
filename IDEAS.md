I cannot sleep, for I have ideas, and my sleep is disturbed by the existence of
these ideas. So I think I need to jot them down so I can have hope in getting
some sleep.

## Rearchitecturing

The Users table should be moved to b3config.db, and I should think of a better
usage for config\_values. app\_root should be fixed to 'src/..', and app\_db
should be changed to site\_db. (That will allow for better future expandability
to support multiple sites, each of which should have their own SQLite database,
and those are indexed within b3config.db.)

An install step should be added, which checks whether b3config.db exists; if it
doesn't, then it creates it with the necessary schema and inserts a dummy user
into it. (TODO: should probably extract the migrator into a separate component
from Db, which itself shouldn't really be a singleton.)

## Dynamicity

The main b3.php entrypoint need not (and perhaps should not) be accessible to
non-authors; it is placed in a directory which is obfuscated from the public.
When some components need to expose themselves to the blog, the platform should
offer the ability to write out a dynamic PHP file which calls into the B3
framework but bypasses the regular dispatch, instead using a special tailored
one.

Example: b3ws.php, for supporting WebSub. Contents:

    <?php declare(strict_types=1);
    namespace PN\B3;
    require '/path/to/aboslute/b3/vendor/autoload.php'; // burned-in
    App::getInstance()->dispatchComponent('b3.websub');

This file is treated as a regular target by the B3 rebuilding system and can be
regenerated if the B3 instance itself is moved.

Seems like a more component-like nature wouldn't hurt the app itself too.

## Error Reporting

There should be a Developer Log somewhere, storing request-scoped metadata that
is collected while running the request, such as warnings and deprecation
notices. This can be persisted unto a special table within b3config.db, which is
occasionally garbage-collected.

// Inspiration: Symfony's Debug component -- TODO: see how they do it

## Event Streams

When the interface is more fleshed out, event streams might prove to be an
interesting progress reporting concept. A long-running operation might be
triggered by JS within the client, the response to which is an Event Stream,
whose events get processed by the client-side JS and a progress bar is displayed
accordingly.

## Cron

Pretty self-explanatory. The job list resides in b3config.db, and it can be
invoked manually or by a cronjob (duh.) A bin/cron.php script should be provided
for triggering from crontab.

Stuff like VACUUMing the databases could go here. (Perhaps that should be
triggered only after the file has grown a certain percentage? So TODOlvl2:
persistent state storage for cronjobs?)

## Plugins

I cannot work on B3 without thinking of it as "WordPress done right", so perhaps
some thought should go into how a plugin system could come into play later.

### Hooking

The WordPress-pioneered action hooking architecture seems promising. Something
similar could be pretty nice to use. (Symfony-like heavyweight event dispatching
is okay for the Java world, but B3 is much closer to Perl in its nature and
therefore should feel more dynamic than enterprisey. So the events should be
simple strings.)

// There's starting to be demand for something like this for internal purposes
too, like cleaning out Session::flash items upon request cycle finishing.

### Registering

b3config.db can come in handy here too. The registration data can be persisted
unto a special table and loaded during requests. I should look more into how
WordPress does it (I believe there are special hooks that get run once when the
plugin is enabled, and their results are persisted and just unserialized upon
requests; TODO study WP and MT.)

### Content Types

The initial release will only support a single content type, the Post, which is
a long-form article that is displayed within the index and the feed, and is
rendered into a single static file.

If one wants to support other content types, they can be provided via plugins.
The individual types can optionally *not* map onto a single disk file but just
exist within indexes and collections. (TODO: flesh out the idea of collections.)

TODO: see how WP does it. Does MT support non-entry types in later versions?

### Hooking into Admin Panel

TODO: study WP, MT.
