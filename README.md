# baryllium
BA student portal. As a token of our appreciation, we christended the BA the "Beurlaubung auf Antrag".

[![Teaser screenshot](https://github.com/blutorange/baryllium/blob/master/planning/screens/moose03.png)](https://github.com/blutorange/baryllium/blob/master/planning/screens/moose03.png)

This is a small project we are doing as part of our education at the university Berufsakademie (BA).
Part of the course involves managing software development and in particular, working with and getting experience with
agile software development methods and tools.

Please see [the wiki](https://github.com/blutorange/baryllium/wiki) for more information, installation instructions,
a roadmap and more.

# Roadmap, story cards, issues

There are also some [story cards on the projects page](https://github.com/blutorange/baryllium/projects) you can view.

The roadmap on the other hand contains various brainstormed ideas. Story cards are more specific requirements, but from a user's perspective.

Github issues are meant for more technical issues that may involve bugs, refactoring or figuring out how to implement a certain requirement or how to access another web service.

# Installation

Please refer to the [wiki](https://github.com/blutorange/baryllium/wiki/Install-and-usage) for more in-depth instructions.

But briefly, you will need to clone

> $ git clone https://github.com/blutorange/baryllium

the repository, install [composer](https://getcomposer.org/) and run an update. Put the application on your web server
of choice.

Create a new database and a user with permissions to that database. Create a `FIRST_INSTALL` in the projects
root directory and point your browser to `/private/php/setup/setup.php` in your browser.


# Security concerns

Configure your web server to block access to all files except `/public/*` and `/resource/*`, as well as `/index.php` and
the several icons (favicons) in the root directory.
