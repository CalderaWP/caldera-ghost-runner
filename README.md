# caldera-ghost-runner
Utility plugin for Caldera Forms development.

This plugin pulls in all of the tests forms for Caldera Forms and also runs the tests and manages keeping the right version of Caldera Forms is installed.

It is designed for two types of sites:
1. A site being used for Caldera Forms QA/CI.
1. A local development site used to develop Caldera Forms.

This is mainly desgined for internal use, but if you wish to learn or borrow from this -- go ahead, GPL.

## Full Documentation
[You Must Be Space Astronaut Grade 1 (SA1) Or Higher](https://drive.google.com/open?id=0B9qEKQe8auJ5VUxFS3otTEdvbGc)

## TL;DR
###Set Some Constants Or Env Variables in wp-config
[Sample SA1](https://drive.google.com/open?id=0B9qEKQe8auJ5OXZXSkZRamlKTjQ)

```
  define( 'CGRKEY', 'Your Ghost Inspector API key' );
  define( 'CGRGDID', 'The address of the Google Sheet With Your Tests' );
  define( 'CGRLOCALAPIKEY', 'LongAlphanumericString' );
```

### Setup wp-admin
* Activate WP Pusher
* Activate Caldera Forms (install with WP Pusher or WP Rollback according to docs)
* Activate this plugin.
* Go to Ghost Runner menu
* Click the *Import Forms* button/link/whatever
* Check that you have a bunch of pages with tests
* Set the branch in the branch setting (this doesn't do much right now, will allow for plugin updates automattiacally later)

### Use Plugin
Read docs.

## Copyright, License, Etc.
Copyright 2017 Josh Pollock for CalderaWP LLC. Licensed under terms of the GNU GPL v2 or later.
