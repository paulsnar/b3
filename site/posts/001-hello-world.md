---
title = "Hello World"
date = "2019-08-06 21:54:30 +03:00"
---

Well then. Here we are.

It's been a while since I've had a blog. There has been more than one occasion
when I wished I had one, but, alas, I didn't.

I suppose that's set to change now. I've gleaned just enough inspiration to push
this thing over the edge, and you're reading the result of that now.

## Tech

Due to the fact that every web developer must go through the process of writing
their own CMS at least once, here we are, I've done it now. Right now it's
little more than a static site generator (as is the trend), but I do intend on
giving it some dynamic smarts _à la_ [Movable Type][].

[Movable Type]: http://web.archive.org/web/20011202193043/http://movabletype.org:80/

## Subscribing

I've noted a trend lately where people who write their own CMS[^1] from scratch
often don't include support for feeds. I can understand that since the number
of people who use feeds for subscribing to sites has been dwindling ever since
social media has become the conduit for internet content, but I want to buck
this trend as far as I can.

[^1]: In this case I'm lumping static site generators as CMSes, even though this
      is not perfectly accurate. Please bear with me.

RSS and feeds in general still remain the [plumbing][inessential-rss] of the
Internet, and large companies who aren't really keen on the freedom of
information circulation that feeds provide still [keep the plumbing
running][tedium-feedburner], even if the system itself appears to be frozen in
time since 2010 or so.

Hence this site has a proper [JSON Feed][self-jsonfeed] from the get-go, and
I'm considering adding an Atom one. Do let [me][] know if you want that done
sooner rather than later.

[inessential-rss]: https://inessential.com/2013/03/14/why_i_love_rss_and_you_do_too
[tedium-feedburner]: https://tedium.co/2017/11/14/google-feedburner-rss-history/
[self-jsonfeed]: https://pn.id.lv/blog/feed.json
[me]: https://pn.id.lv

## Form

There are many blogs out there, most of which just contain long-form content.
I've been having some thought experiments on how to better share content of
other kinds, and I hope for this blog to eventually be an avenue for that.

## Sharing

On the basis that most of these blog-like things built by software engineers
tend to be open-source, I suppose I have no choice but to follow. So the source
for this site [is hosted on Github](https://github.com/paulsnar/b3), to where it
is [mirrored][mirroring] from a private [Gitea](https://gitea.io) instance.

[mirroring]: https://pn.id.lv/blog/2019/08/gitea-mirroring

When the platform becomes more dynamic, I won't be able to persist all content
to the Git repo, but I'll strive to keep the underlying tech open regardless so
it may find some use elsewhere.

## Easter egg

If you're on Firefox, go to View -> Page Style.
