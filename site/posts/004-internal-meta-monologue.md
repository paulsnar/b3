---
title = "Internal Meta-Monologue"
published-at = "2019-08-13 22:40:00 +03:00"
---

Okay, so I have brought the first incarnation of [b3][] to a point where it can
sustain blogging as far as I need it. There are still some things that are on my
to-do list that I want to do, but I can't actually bring myself to make progress
on any of them.

[b3]: https://github.com/paulsnar/b3

Therefore I'm writing this document as a sort-of manifesto which shall dictate
my future course of action vis-à-vis this blog and the software that makes it
tick.

b3 hasn't actually turned out to be what I intended for it to become. After a
day or two of [procrastinating][accountingnightmare-aceattorney], I've pulled
together my thoughts on what went wrong and how will I fix it.

[accountingnightmare-aceattorney]: https://www.youtube.com/playlist?list=PL3oxWRzqaDIttX-46RgIRzbeo8TEhb1OA

In a recent email to [an inspiration for this blog][kabirshah], I described the
workflow used by most other technical bloggers nowadays: _the post gets written,
the site gets rendered via a static site generator such as Jekyll, and the
result gets checked into a Git repo and pushed to deploy._ This approach works
for many, but it also involves a lot of friction which is especially noticable
when it comes to publishing smaller pieces of content, e.g., microblogs or
linklogs. I've found this friction is also enough to cause me to abandon any of
my online writing efforts soon after getting the initial sketch up, as it is
starting to happen now as well.

[kabirshah]: https://blog.kabir.sh/

Ergo I needed a model which imposed less friction, such as the one used by fully
dynamic CMSes, for instance, [Wordpress][wordpress]. The idea that the content
is published directly and immediately, as well as the ability to have a nice
instant preview and other such functionality, makes for a pretty low-friction
model, which is why [some technical bloggers continue to use
Wordpress][vrkdev-newblog] even for new blogging efforts.

[wordpress]: https://en.wikipedia.org/wiki/WordPress
[vrkdev-newblog]: https://www.vrk.dev/2019/03/16/okokokok-new-blog/

On the other hand, most such CMSes have their deficiencies: they're often quite
heavyweight, and under most configurations every request creates a hit to the
database. I wish to have the posts themselves be fully static and the CMS itself
to have the absolute minimum of code to implement this.

Taking all of this into account, some adept and experienced[^1] readers might
notice that what I strive for is similar to what's provided by [Movable
Type][movabletype]. I really like Movable Type in principle, especially the
older versions (1.1 and thereabouts), but unfortunately, I can't use Movable
Type itself since it is [quite expensive][movabletype-buy] and also it doesn't
align well with my philosophy of using open-source software where at all
possible. Besides, I probably wouldn't be actually able to use version 1.1 in
any legal manner now.

[movabletype]: https://en.wikipedia.org/wiki/Movable_Type
[movabletype-buy]: https://movabletype.com/#buy

[^1]: By which I mean anyone who was around during 2001 or so, when the
  _"blogosphere"_ had its heyday.

It seems to me that implementing what I consider to be a simple CMS can't be too
difficult, so my current stance is that I'm going to reimplement Movable Type by
myself. The next version of b3 is intended to be a static-site generator whose
primary interface is the browser; basically the best of both Wordpress and
Movable Type.[^2]

[^2]: As of now I don't intend to add any features I don't personally need, such
  as a WYSIWYG editor or a proper plugin system. I write my posts in a dialect
  of Markdown and need only basic features so the initial release will be as
  lightweight as possible. That's not to say that this can't change, though.

For you, the reader, little will change. Actually, it's my goal for nothing to
change as of now---the major improvement is for me as the writer. In the future
this system might allow me to add more dynamic features to the site , but it
might be too early to speculate about that.

I do intend to continue developing b3 [in the open][b3], so in the unlikely
case you're seeking a CMS for use of your own and are not repulsed by the use
of PHP, even though I'll excuse it as being the more pragmatic choice for me,
perhaps I might interest you in trying b3 out.
