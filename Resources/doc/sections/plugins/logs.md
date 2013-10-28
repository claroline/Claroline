Event Tracking
==============

This section cover the uses of the mechanisms of event log in the plugins.

Creation of logs
----------------

There are two ways of creating event logs in plugin:
 * Using existing event provided by the CoreBundle
 * Create custom event

Using existing one is quicker but the way it's displayed cannot be change.
And if you use them you won't be able to award badge based on specific plugin's
action, just generic one provided by the CoreBundle.
Indeed this event is used in the badge system to know which action can be used
to award badge.

Knowing that creating custom event is highly recommended.
For create custom event go see the [event creation][5] documentation and for
using existing one please refer to the CoreBundle code.

Displaying of custom logs
-------------------------

See the section on [event-tracking][4] to know how to display your custom event.

----------

Return to :

- [core documentation][1]
- [index documentation][2]
- [plugin documentation][3]


[1]: ../core.md
[2]: ../../index.md
[3]: ../plugins.md
[4]: ../event-tracking.md#create_new_event
[5]: ../event-tracking.md#displaying_event
