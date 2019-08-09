---
type = "blog"
title = "On SQLite"
date = 2019-08-07T16:00+03
state = "draft"
---

# On SQLite

> Software engineering appears to be [fueled mostly by FUD][hn-harmful].

[hn-harmful]: https://hn.algolia.com/?query=considered%20harmful&sort=byPopularity

Recently I've had a conversation with an entrepreneur who suggested that they've
decided to use MariaDB for their still work-in-progress site as opposed to
SQLite on advice that "SQLite is not designed for production". While there is a
kernel of truth in that, I feel like this is more often than not an opinion born
out of stereotypes, one that is borrowed without having any deeper
justification or having done enough (if any) research.

As this site is intended for me to publish my opinions, here is one I've formed
based on some research and experience.

## Know your requirements

Before I start talking your ears off about why this stereotype is not as sound
as it seems, [^1] you should know at least intuitively what sort of
requirements you're facing for the project you need a database for. Will the
workload be read- or write-intensive? How many concurrent users are you
expecting to have in the nearest future? Is an RDBMS or a document database the
better fit for the data model? [^2]

[^1]: Pun intended.
[^2]: If you don't need an RDBMS, stop reading this post now. :)

Now, if your project is not particularly heavy on writes and you're not
expecting super-hockeystick growth for a couple of weeks at least, _it's
probably fine to use SQLite for the start,_ by which I mean the prototyping and
MVP phase. It might even suit you alright for a while after that.

A common myth that is voiced often as to why you should stay away from SQLite
like the plague is that _"SQLite doesn't support any writer concurrency."_ This
was undeniably true back in the day when doing any write transaction [required
to halt all activity to the database][sqlite-lockingv3], but since version
3.7.0 SQLite supports using a [write-ahead log][sqlite-wal], which allows for
quite a bit of concurrency while also preserving the ACID semantics that SQL
databases are known for. If you read the [WAL documentation][sqlite-wal],
you'll notice that many improvements have been made since version 3.7.0, which
was released back in 2010, so it's likely many of them should be available
within your machine's local SQLite library.

[sqlite-lockingv3]: https://www.sqlite.org/lockingv3.html
[sqlite-wal]: https://www.sqlite.org/wal.html

Another reason to perhaps choose SQLite over other RDBMSes is that it is
[tested to a level not a lot of other software can even
approach][sqlite-testing], which might mean that your data might be more safe
within SQLite than some other databases. [^3] SQLite has been checked against
all possible circumstances that could cause it to fail, and its behaviours have
been defined with data safety and integrity in mind, as one would expect from
a database.

[^3]: Do make backups though.

[sqlite-testing]: https://www.sqlite.org/testing.html

A slightly more esoteric and maybe less production-useful fact is that SQLite
is heavily introspectable, in particular, any query is compiled down to a
[bytecode program][sqlite-opcode] which is then executed by the VDBE. This is
something my inner engineer obsesses about because the bytecode interpreter
alone [is more advanced][sqlite-opcode-initcoroutine] than some proper
programming languages. With [some effort][sqlite-opcode-theopcodes] it's
possible to fully understand how a particular query will be run and optimize it
accordingly. The intuition for database performance this helps develop also
comes in handy when working with other database systems.

And in some cases, [SQLite can outperform client/server
RDBMSes][sqlite-np1queryprob] by the virtue of _not_ being client/server.

[sqlite-opcode]: https://www.sqlite.org/opcode.html
[sqlite-opcode-initcoroutine]: https://www.sqlite.org/opcode.html#InitCoroutine
[sqlite-opcode-theopcodes]: https://www.sqlite.org/opcode.html#the_opcodes
[sqlite-np1queryprob]: https://www.sqlite.org/np1queryprob.html

## Not a silver bullet

Even taking the above into account, there's a reason why SQLite's [Well-Known
Users][sqlite-famous] page doesn't really list any particular web enterprises
apart from three programming languages that have good bindings to SQLite.

SQLite has its limitations, even with WAL, because it has to play nice with a
single file being accessed by multiple independent processes. The recommended
reasoning for choosing for or against SQLite is outlined [within their own
documentation][sqlite-whentouse].

A non-performance-related detriment is that SQLite has an
[interesting][sqlite-datatype3] interpretation of the SQL standard as it
relates to data typing. If you need to move away from SQLite, hopefully you
haven't been relying on the fact that it is not only possible to store a
non-numeric string value within an integer column, but that the returned type
[will be a string.][sqlite-datatype3-typeaffinity]

[sqlite-famous]: https://www.sqlite.org/famous.html
[sqlite-whentose]: https://www.sqlite.org/whentouse.html
[sqlite-datatype3]: https://www.sqlite.org/datatype3.html
[sqlite-datatype3-typeaffinity]: https://www.sqlite.org/datatype3.html#type_affinity

## Overinvesting

Notwithstanding all the aforementioned technical arguments, there is a different
trend at play that causes people to make a similar choice against SQLite.

> SQLite cannot scale.

[You are not Google.][not-google] You'll probably be fine.

It is true that SQLite cannot "scale," but don't take offence in me doubting
that you'll have a particularly large need of scaling in the first place. There
are 

___TODO.___

[not-google]: https://blog.bradfieldcs.com/you-are-not-google-84912cf44afb
[adamdrake-fasterthanhadoop]: https://adamdrake.com/command-line-tools-can-be-235x-faster-than-your-hadoop-cluster.html

## But don't actually take advice from me

And yet, after all that, I'll suggest to you to not actually use SQLite. That's
right, the premise of this article was an outright lie!

I don't believe it's worth using one technology over another just on the advice
of random internet strangers. Nor should you. I believe in making an informed
choice based on well-defined requirements and researched arguments.

I hope this post has given you arguments as to why you might want to consider
using SQLite before going for something more heavyweight. Either choice is okay,
given that you can justify it to yourself and, hopefully, to others as well.

Perhaps using something like MariaDB or PostgreSQL by default is better for the
particular use case you have. But do consider starting out with something like
SQLite before investing needless resources into managing a full-blown RDBMS
when you still have but a single process communicating with it.

---

This post was born out of a conversation with a team responsible for building a
particular new product that hasn't even really reached an MVP stage yet, and
their choice of database system was informed mostly by the fact that "SQLite
cannot scale." I'm not saying that's not true, because with some squinting it
is, but was it really worth worrying about scaling your database without anyone
using it yet?

I have a good hunch that SQLite would've been just fine for them for a while,
especially because from what I infer they used it on a non-critical part of
their infrastructure.

Of course, using an RDBMS is not the same as using Hadoop for storing the data
for your twenty users, and in that case do choose an RDBMS over your [webscale
solution][mongodb-webscale].

[mongodb-webscale]: https://www.youtube.com/watch?v=b2F-DItXtZs

[^4]: I recommend PostgreSQL. [Uber used it][uber-postgresql]
      [until][postgresql-uber] [they became Google][uber-schemaless].

[uber-postgresql]: https://use-the-index-luke.com/blog/2016-07-29/on-ubers-choice-of-databases
[postgresql-uber]: https://www.postgresql.org/message-id/579795DF.10502%40commandprompt.com
[uber-schemaless]: https://eng.uber.com/schemaless-part-one/
