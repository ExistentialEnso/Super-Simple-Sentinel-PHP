Super-Simple-PHP-Auth
=====================
* Author: Thorne Melcher
* License: MIT
* Version: 0.4

If you want a one file include, no database required authentication system for PHP, this will do the trick. Just edit the variables beginning with $config in the top of auth.php.

Usage:
    require_once("auth.php");

That's it!

Version History
===============
* v0.4 (3/11/12) - Added basic support for hashing, as well as included a commented-out, optional, example SQL integration (though this was always possible)
* v0.3 (3/10/12) - Added basic brute force protection (using sessions) and an option to enable case insensitive usernames.
* v0.2 (3/9/12) - Added support for multiple accounts.
