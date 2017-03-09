# baryllium
BA student portal. As a token of our appreciation, we christended the BA the "Beurlaubung auf Antrag".

This is a small project we are doing as part of our education at the university Berufsakademie (BA). Part of the course involves managing software development and in particular, working with and getting experience with agile software development methods and tools.

Please see [the wiki](https://github.com/blutorange/baryllium/wiki) for more information, installation instructions, a roadmap and more.

# Roadmap, story cards, issues

There are also some [story cards on the projects page](https://github.com/blutorange/baryllium/projects) you can view.

The roadmap on the other hand contains various brainstormed ideas. Story cards are more specific requirements, but from a user's perspective.

Github issues are meant for more technical issues that may involve bugs, refactoring or figuring out how to implement a certain requirement or how to access another web service.

# Installation

Please refer to the [wiki[(https://github.com/blutorange/baryllium/wiki/Install-and-usage) for more in-depth instructions. But briefly, you will need to clone

> $ git clone https://github.com/blutorange/baryllium

the repository, install [composer](https://getcomposer.org/) and run an update. Create a new database and user with permissions to that database and configure the database (`baryllium/config/phinx.yaml`). For now you need to run the Doctrine2 script manually to initialize the database schema. Then you can put the application on your web server of choice and use it.

# Security concerns

Revoke public access for the `baryllium/config` and `baryllium/php/private` directories. Clients should not be able to request or access any php scripts there directly.
