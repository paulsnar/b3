# b3

Static-file blogging engine that doesn't require interacting with it within the
command line. Combining the good parts of WordPress and Jekyll.

## Installation

Requirements: `sassc`, `composer` and PHP of at least 7.2 or so.

Currently this process is extremely bare-bones, but here goes:

* Get the files of this repo onto your server. Only the files of the `public`
  directory need to be world-accessible, so it can be symlinked onto your
  document root.
* Within the project folder, run `composer install` and `make`.
* Run `php bin/create-user.php` to create your user.
* Open `b3.php` within your browser.

From there feel free to explore. There aren't many options there, but it should
be enough.

More documentation is in order (like a manual), especially regarding templates.

## TODO

* [ ] More post metadata, editing post dates.
* [ ] Implement ability for extensions to register dynamic components --
  generate PHP files which call into b3 from the public root.
  * [ ] Document the extension interface itself.
* [ ] Rework the admin panel design.
* [ ] Implement ACLs (currently all users are equally powerful).

Feel free to work on any of these. I haven't had a particular need for these yet
so they're not present right now.

## Name

It's [b2][] plus one.

[b2]: https://github.com/WordPress/book/blob/a922c52103f1c843686370408319065c9230038b/Content/Part%201/2-b2-cafelog.md

## License

[ISC](./LICENSE.txt)
