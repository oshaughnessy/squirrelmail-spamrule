Spam Rule is a plugin for SquirrelMail that lets you select from different
groups of email filters via a config file maintained through FTP.  It is
written specifically for situations where a user can select from various
filters, individually or in predefined groups, and subscribe to them by
modifing lines in a procmail or maildrop config file in their home
directory.  This way the user can have his mail filtered at message delivery
time by the email system rather than at reading time by SquirrelMail, which
could really slow things down.


## Requirements

- access to an FTP server for each IMAP user, using the same login name
- a forward file somewhere under the user's FTP directory,
  e.g. ".forward" or ".qmail"
- a mail filter config file somewhere under the user's FTP directory,
  e.g. ".mailfilter" for maildrop or ".procmailrc" for procmail
- version 2.x of the compatibility plugin


## Features

- creates a new block under Options called "Spam Rules"
- tries to give the user a simple interface that can still provide as much
  flexibility as the user desires
- lets the admin create sets of predefined filter groups from which the
  user can select, but also lets the user pick and choose from the whole
  set of available filters
- tries to be very flexible (perhaps too much so?)
- support for translations


## Installation

- Install this directory into your squirrelmail source dir at
  plugins/spamrule.
- Copy config.php.ex to config.php and modify the file as described within.
  You may also want to leave this file intact and simply put your own
  variable settings into config_local.php instead, which will override
  anything defined in config.php.
- Take a look at the maildrop subdirectory.  There's a sample of my
  system-wide maildrop config, /usr/local/etc/maildroprc, and individual
  rule files that get installed under /usr/local/etc/maildroprcs.  This
  plugin will include the ones that a user selects by customizing his
  .mailfilter file.  You can add your own by changing config.php, but
  it's already set up to take advantage of the ones I've included.
- Enable the plugin through conf.pl.
- Test it out!  Make sure to double-check the contents of your forward file
  and mail filter config file, as well as successful results of message
  filtering and delivery, before putting it into production.


## Upgrading

- If you've used spamrule in the past, you can now override config.php's
  settings in config_local.php.  Any variables defined in config.php
  can be put there.  Since it's loaded after config.php, your settings
  will override the defaults.
- Release 0.4 added internationalization and included major updates to
  the strings in config.php.  I strongly recommend that you don't just
  keep your old pre-0.4 config, but rather copy config.php.ex to
  config.php and add your new settings there or to config_local.php.


## Questions and support

I've tested this plugin quite a bit and it has worked fantastic on my
systems since 2002.  I have tried to make it resistant to unexpected
problems and easy to change for different environments, but I'm sure
there are many shortcomings.  If you have any questions or comments,
please contact me through the project's Github page at
https://github.com/oshaughnessy/squirrelmail-spamrule.


## Copyright

Copyright (c) 2002-2013 O'Shaughnessy Evans <shaug-sqml @ wumpus.org>


## License

This code is licensed under the Perl Artistic License, version 2.0.  For
more information, please see the file Artistic_2.0, which was included with
this distribution, or http://opensource.org/licenses/artistic-license-2.0.php
