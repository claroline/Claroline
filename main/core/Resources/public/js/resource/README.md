Manager (manager.js)
--------------------

Entry point (singleton). Responsible for initialization of the main components
(dispatcher, server, router, master view(s)), both in picker and "full" mode.

Views
-----

No direct access/communication outside dispatcher events. Only coupling is at
initialization time (composition).

Composition tree:

    Master
        Breadcrumbs
        Actions
            Filters
        Nodes
            Thumbnail
        Form
        Rights
        Confirm

Events
------

View events (dom) vs dispatcher events ("outer" events, scoped if needed).

Dom
---

No access outside view element. Access inside view only through cached/scoped element (-> this.$(selector)).

Ajax
----

No direct calls (-> trigger to server.js and listen back).

Debugging
---------

Use the manager "logEvents" method to track event dispatching in real-time.
