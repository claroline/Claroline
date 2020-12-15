Notification System
==========================


What it does?
-------------------------

Notifies users on new events or activities. The idea was to develop a system which behaviour would be similar to other known notification systems (facebook’s notifications, google+ notifications etc.).


Features
------------------------

*	Tied up with Log system (log events can generate notification)
*	Works with Listeners to display notifications
*	Builds a view template using the entity’s attributes
*	Has its own translation domain (notification.lang.yml)
*	To avoid system overload, only notifications that are less than 2 months old are stored.


Template
----------------------

![Notification template](img/notification_template.jpg "Notification template")

A notification is divided in four parts:
*	The avatar of the user whose action raised the notification (square 1)
*	A short text describing the action (ex. John Doe enrolled you in “Anatomy” as “Student” if the doer is a user or if the doer is the platform: You have been enrolled as “Collaborator” in “Anatomy”)
*	An icon depending on the notification type (blog icon if action refers to a blog resource, wiki icon if action refers to a wiki resource, platform icon if is a generic action etc.). This icon is generated automatically using the name of plugin.
*	The date the action took place (square 2), presented in a sort of “smart” format (3 hours ago, yesterday at 10:30, November 30 at 15:10 etc.) 

Below it’s given a concrete example of the notification representation:

![Notifications example](img/notifications_mockup.png "Notifications example")


List of actions that can push a notification
--------------------------------------------------

The actions that raise a notification can be divided in two categories, depending on the level of the target entity. Thus, we have the notifications happening inside and outside a workspace and those happening inside a resource.

### Workspace level notifications

A user receives a notification only if he is enrolled to the workspace.

*	**Enroll group to a workspace**: Notifies the group users
*	**Enroll user to a workspace**: Notifies the target user
*	**Give access to a resource**: Notifies users that they can access the resource


### Resource level notifications

If a user wants to receive notifications of actions taken place inside a resource (plugin) he has to enable the functionality in the resource options (option “Notify me”). 
This notification category, contains all those notifications defined by inside each plugin. (Post creation in a Forum, Article creation in Blog, new comment in post etc.)


### Structure (Entities, Relationships, Tables)

In the diagram below it is shown the table structure for the Notification System. As we can see there is the Notification table (entity) linked though a many to one relationship with the user (doer, the one whose action resulted the notification) but also linked through a one to many relationship with the NotificationViewer table (entity). The NotificationViewer is essentially the intermediate entity that connects a user (viewer) to a notification (view) as a user can receive many notifications and a notification can be diffused to many different users. There is a status attribute in the NotificationViewer class that changes when the notification has been seen by the user. Thre is also the FollowerResource entity that connects a user to a resource and informs a system that a specific user follows a resource (has enabled notifications for this resource).

The User and Resource entities are not defined inside the plugin. They are external entities used by the plugin. As such, they must provide the following methods: getId(), getPicture(), getFirstName(), getLastName() for User entity and getId(), getClass() for Resource entity. 

![Notifications system UML](img/notifications_system_uml.jpg "Notifications system UML")


### Implementation

The implementation of the notification system is achieved through the “Notifiable” interface. Every log event that can raise a notification must implement the notifiable interface. This interface contains all the information about the users that need to be informed, (if the notification will be sent to the resource’s followers, it can include other users as well or even exclude ones). It also contains all necessary data to create a notification (date, action, icon etc.).

![Notifiable class](img/notifiable_class.jpg "Notifiable class")


### Future expansion (Real Time Notification Push and API)

This expansion is about adding a real time functionality to the Notification System. This functionality will be developed but it will stay optional, because for a real time system we need a “push” server (NodeJS or other). If a NodeJS server is installed then it would be possible to enable the functionality in the plugin’s parameters. The necessary files and functions would be present all the time, but loaded and called only if the real time option is enabled.

A future expansion will also provide with an API to receive User notifications.
