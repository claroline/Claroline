NotificationBundle
==================

Notification bundle for Claroline Connect. See https://github.com/claroline/Claroline

Installation
-------------------------

This bundle is a required bundle from CoreBundle. It is needed so the Core can work properly.

However if you want to install it manually here is the package in composer:

`composer require icap/notification-bundle "dev-master"`

Prerequisites
------------------------

*	User class must provide methods: getId(), getFirstName(), getLastName() and getPicture() (user's avatar)
*	Resource class must provide methods: getId(), getClass() 
*	In config.yml the following fields need to be informed:
    ** default_layout : the layout from which the notification list page will extend (default ClarolineCoreBundle::layout.html.twig)
    ** max_per_page : the maximum number of notifications per page in notification list page (default 50)
    ** dropdown_items : the number of notifications present in the dropdown list (default 10)
    ** system_name : the system's name; used when action has no doer (default Claroline)

How to use in plugins
-----------------------------

