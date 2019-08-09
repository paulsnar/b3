---
title = "Hello World"
date = "2019-08-06 21:54:30 +03:00"
state = "draft"
---

# Hello World

Well then. Here we are.

It's been a while since I've had a blog. There has been more than one occasion
when I wished I had one, but, alas, I didn't.

I suppose that's set to change now. I've gleaned just enough inspiration to push
this thing over the edge, and you're reading the result of that now.

## Tech

Due to the fact that every web developer must go through the process of writing
their own CMS at least once, here we are, I've done it this time. As is the
trend these days, it's a static site generator, but with some dynamic smarts
added onto it. If you remember Movable Type, there are some parallels to be
drawn.

Right now it's just the bare minimum to get me started because by `$deity` I
don't need another attempt at this failing spectacularly. But in any case I hope
this attempt will be better.

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
[self-jsonfeed]: /feed.json
[me]: https://pn.id.lv

## Form

There are many blogs online, and most of them are of a simple form --
ocasionally the author drops a long-form written piece, and that's it. I've had
some ideas about how I could share other content I come across, especially since
I happen to stumble upon a lot of things I'd love to share with others.

Well, I thought a blog-like thing would be a perfect avenue for sharing links.
So that's what I'm doing.

## Copyright

On the basis that most of these blog-like things built by software engineers
tend to be open-source, I suppose I have no choice but to follow. So the source
for this site [is hosted on Github](https://github.com/paulsnar/b3), to where it
is [mirrored][mirroring] from a private [Gitea](https://gitea.io) instance.

[mirroring]: /posts/2019/08/gitea-mirroring
