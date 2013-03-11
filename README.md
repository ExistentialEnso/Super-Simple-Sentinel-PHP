Super-Simple-PHP-Auth
=====================
* Author: Thorne Melcher
* License: MIT
* Version: 0.3

If you want a one file include authentication system for PHP, this will do the trick. Just edit the variables beginning with $config in the top of auth.php.

Usage:
    require_once("auth.php");

That's it!

Version History
===============
* v0.3 (3/10/12) - Added basic brute force protection (using sessions) and an option to enable case insensitive usernames.
* v0.2 (3/9/12) - Added support for multiple accounts.
