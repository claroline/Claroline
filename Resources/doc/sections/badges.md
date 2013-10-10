Badge system
============

Claroline provides a badge system based on the [Open Badge][3] standard from [Mozilla][4].

Create badges
-------------

To create a badge you need to provide some mandatories datas :

- **version**: The version of the badge
- **name**: Human-readable name of the badge being issued. Maximum of 128 characters.
- **image**: Path to the image representing the badge. Should be a square and in PNG format. Maximum size is 256kb. Minimum size 64*64.
- **description**: Description of the badge being issued. Maximum of 128 characters.
- **criteria**: Information describing the badge and criteria for earning the badge (not the specific instance of the badge).
- **expired_at** (optional): Date when the badge expires. If omitted, the badge never expires.
    - The badge is not removed from the earner’s Backpack after the expiration date; there will be some visual/technical indicator that the badge is expired and needs to be re-upped. Must be formatted "YYYY-MM-DD" or a unix timestamp.

Manage badges
-------------

Badge can be issue to a user.

A user can ask for earning a badge.

A badge manager must issue him the badge if criteria is reunited.
Validation is now manual, but there will be an automatic process in the future.

Rules can be added to a badge.
This rules will be used to determine if a badge can be awarded or not, automatically or not.
By the way you can now specify if a badge can be automatically awarded or not.


----------

Return to :

- [core documentation][1]
- [index documentation][2]


[1]: core.md
[2]: ../index.md
[3]: http://openbadges.org/
[4]: http://www.mozilla.org/