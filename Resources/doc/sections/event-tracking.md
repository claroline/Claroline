Event Tracking
==============

Event Tracking is a system that you can use to record user interaction with
platform.

This is accomplished by attaching the appropriate event to the particular action
you want to track.

When used this way, all user activity on such actions is calculated and
displayed as Events in the Tracking reporting interface.

Event Tracking system can collect five major event categories:

 * User events (user create, update, delete etc.)
 * Group events
 * Resources events
 * Roles events
 * Workspace events

Exhaustive core events list
--------------------------
### User events ###
* `user-create`
* `user-delete`
* `user-login`
* `user-update`

### Group events ###
* `group-add_user`
* `group-create`
* `group-delete`
* `group-update`

### Workspace events ###
* `workspace-role-subscribe_user`
* `workspace-role-subscribe_group`
* `workspace-role-unsubscribe_user`
* `workspace-role-unsubscribe_group`
* `workspace-create`
* `workspace-delete`
* `workspace-role-change_right`
* `workspace-role-create`
* `workspace-role-delete`
* `workspace-role-update`
* `workspace-tool-read`
* `workspace-update`

### Resource events ###
* `resource-copy`
* `resource-create`
* `resource-custom_action`
* `resource-delete`
* `resource-export`
* `resource-move`
* `resource-read`
* `resource-shortcut`
* `resource-update`
* `resource-update_rename`

Not-exhaustive plugins events list
--------------------------
### Dropzone events (plugin) ###
* `resource-icap_dropzone-correction_end`
* `resource-icap_dropzone-correction_start`
* `resource-icap_dropzone-correction_validation_change`
* `resource-icap_dropzone-criterion_create`
* `resource-icap_dropzone-criterion_delete`
* `resource-icap_dropzone-drop_end`
* `resource-icap_dropzone-drop_open`
* `resource-icap_dropzone-drop_evaluate`
* `resource-icap_dropzone-drop_start`
* `resource-icap_dropzone-document_delete`
* `resource-icap_dropzone-dropzone_update`

### Blog events (plugin) ###
* `resource-icap_blog-configure`
* `resource-icap_blog-comment_create`
* `resource-icap_blog-comment_delete`
* `resource-icap_blog-post_create`
* `resource-icap_blog-post_delete`
* `resource-icap_blog-post_read`
* `resource-icap_blog-post_update`

### Wiki events (plugin) ###
* `resource-icap_wiki-contribution_create`
* `resource-icap_wiki-section_create`
* `resource-icap_wiki-section_delete`
* `resource-icap_wiki-section_move`
* `resource-icap_wiki-section_remove`
* `resource-icap_wiki-section_restore`
* `resource-icap_wiki-section_update`
* `resource-icap_wiki-configure`

### Lesson events (plugin) ###
* `resource-icap_lesson-chapter_create`
* `resource-icap_lesson-chapter_delete`
* `resource-icap_lesson-chapter_move`
* `resource-icap_lesson-chapter_read`
* `resource-icap_lesson-chapter_update`


Advanced features
-----------------

In this section we cover what we call **advanced features** of the event
tracking process.

These features include:
 * Not repeatable log
 * Mechanisms for displaying plugins'logs


### Not repeatable log ###

Some events such as resourceRead need to be filtered before storing them to the
database in order to avoid information redundancy. By information redundancy we
mean that the same event even if it occurs many times during a specific period
of time it only has to be stored once.

E.g.: Taking the case of a blog for example. In a blog it is quite possible that
a user navigates though it's different pages, posts, comments etc. Every time
the user navigates a **read Blog** event occurs
(since he is loading another page/ item of the same blog).


It is clear that storing all these occurrences is pointless since we only need
one to tell us that a user started reading the resource. For that reason, we
choose to store only the first of these occurrences then wait some time until
we store again the same info. The delay time is configurable in the
parameters.yml file.

The events that are defined as **Not repeatable** are the following:
 * ResourceRead
 * WorkspaceToolRead

The mechanism behind that functionality does NOT use any database interaction.
Instead, last event occurrence is stored in client's http session having as
value a timestamp (when the event first occurred).


If this behavior needs to be applied in other events as well, then the created
event MUST implement the NotRepeatableLog interface. The NotRepeatableLog
interface requires the definition of a method called getLogSignature() which
generates a unique identifier for this event's occurrences. For any resource
the unique identifier would has the following
form:resource_read_abstractResourceID. For example in the case of a Blog whose
id is 42 the generated signature would be: resource_read_42.

Creating new event log
----------------------

There are two ways for creating new event log:
 * Using existing event provided by the CoreBundle
 * Create custom event

Using existing one is quicker but the way it's displayed cannot be change.
And if you use them you won't be able to award badge based on specific plugin's
action, just generic one provided by the CoreBundle.
Indeed this event is used in the badge system to know which action can be used
to award badge.

Knowing that creating custom event is highly recommended.
So only creation of custom event will be covered in this doc, for using existing
one please refer to the CoreBundle code.

For creating new event log here is the step to follow:
 * Create new class that extends the good class
   * AbstractLogResourceEvent when action occured on a resource, or child
     resource
 * Define a constant in the class whose name begin with `ACTION`, other name
   won't be used to list available action's log
   This constant must be well formated in two or three sections separated by a
   dash `-` in order to be used in the filter form.
   Each of this section can match pretty much everything you need.
   Here is an example:
     * first section is the type of object it's associated (platform, resource,
       workspace, role, tools, widget, user...)
     * second section (`optionnal`) is the type of the resource (for resource
       type of object by example)
     * third section is the action executed (login, post_create, post_update,
       post_delete, ws_role_subscribe_user etc...)
   You will need to add some translation text for the first section key and for
   the `log_%constantName_filter`
   (e.g. `log_workspace-update_filter` for update action on a workspace).
   Each section will become a choice list ine the filter form.
 * Use this new class with an event dispatcher on the `log` event name and
   you're done

For now log form filter will just have two select list but you can make three
section for future enhancement.

Let's admit you want to create a log for when you create a post in a blog.
Firs create the event class:

```php
<?php

namespace ICAP\BlogBundle\Event\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use ICAP\BlogBundle\Entity\Blog;
use ICAP\BlogBundle\Entity\Post;

class LogPostCreateEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_blog-post_create';

    public function __construct(Blog $blog, Post $post)
    {
        $details = array(
            'post' => array(
                'blog'  => $blog->getId(),
                'title' => $post->getTitle(),
                'slug'  => $post->getSlug()
            )
        );

        parent::__construct($blog->getResourceNode(), $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE);
    }
}
```

By default all logs are not displayed on vizualisation interface.
To see them you have to define their displaying restirctions.
To do it you have to define the `getRestriction` method on your event log class
and make it return an array of where you want it to be displayed.
There is two constants at your disposal in the class, `DISPLAYED_ADMIN` and
`DISPLAYED_WORKSPACE`.

Somme classes you can extend of exist to ease the class creation, here is the
list and what they are for:
 * `AbstractLogResourceEvent` for event log associate to a resource
 * `AbstractLogToolEvent` for event log associate to a tool
 * `AbstractLogWidgetEvent` for event log associate to a widget

This solution isn't mandatory, but it fills the log with some predetermined
datas that you don't have to deal with.

After creating this class you just have to use it in your code where you create
your post:

```php
$event = new \ICAP\BlogBundle\Event\Log\LogPostCreateEvent($blog, $post);

$this->get('event_dispatcher')->dispatch('log', $event);
```

And you're good.
All parameters provided to the event is specific to this case, you can of course
give what you want to the class, just don't forget to give the superclass what
she needs if you use one of them.

Displaying new event log
------------------------

When creating new event log, listeners and functions have to be defined in order
to render the new logs.

The listeners that need to be defined are the following:
 * create_log_list_item_%actionName%
   (e.g. create_log_list_item_resource-icap_blog-post_create)
 * create_log_details_%actionName%
   (e.g. create_log_details_resource-icap_blog-post_create)
As well as the functions called by these listeners.


The first listener is responsible for rendering the event in an appropriate form
for the events list (short presentation of the event). An example of this
function is given below:

```php
public function onCreateLogListItem(LogCreateDelegateViewEvent $event)
{
    $content = $this->container->get('templating')->render(
        'ICAPBlogBundle::log_list_item.html.twig',
        array('log' => $event->getLog())
    );
    $event->setResponseContent($content);
    $event->stopPropagation();
}
```

The example shows that along with the listener and the function a (twig) view is
also required. In the given example this view is the
**log_list_item.html.twig**, responsible to display the event in a short form.
Below is given the code of this view:

```php
{% set doer %}
    {% include 'ClarolineCoreBundle:Log:view_list_item_doer.html.twig' %}
{% endset %}

{% set blog %}
    {% include 'ICAPBlogBundle:Log:log_list_item_blog.html.twig' %}
{% endset %}

{% set post %}
    {% include 'ICAPBlogBundle:Log:log_list_item_post.html.twig' %}
{% endset %}

{% set resource %}
    {% include 'ClarolineCoreBundle:Log:view_list_item_resource.html.twig' %}
{% endset %}

{% if constant('ICAP\\BlogBundle\\Event\\Log\\LogPostCreateEvent::ACTION') == log.action %}
    {{ 'log.blog.create_post'|trans({'%blog%': resource, '%post%': post}, 'log')|raw }}
{% elseif constant('ICAP\\BlogBundle\\Event\\Log\\LogPostReadEvent::ACTION') == log.action %}
    {{ 'log.blog.read_post'|trans({'%blog%': resource, '%post%': post}, 'log')|raw }}
{% else %}
    no default text
{% endif %}
```

In addition to this listener you need to define a translation text for the key
`log_%actionName%_shortname` in order to display the action text on the list
item row of the log (e.g. for action `resource-icap_blog-post_create` define a
`log_resource-icap_blog-post_create_shortname` key in your log translation
file).

The second listener displays the event in details. Hence, both its function and
its view are more complex.

```php
public function onCreateLogDetails(LogCreateDelegateViewEvent $event)
{
    $content = $this->container->get('templating')->render(
        'ICAPBlogBundle::log_details.html.twig',
        array(
            'log' => $event->getLog(),
            'listItemView' => $this->container->get('templating')->render(
                'ICAPBlogBundle::log_list_item.html.twig',
                array('log' => $event->getLog())
            )
        )
    );
    $event->setResponseContent($content);
    $event->stopPropagation();
}
```

The associated view is the **log_details.html.twig** and its code is given
below:

```php
{% extends 'ClarolineCoreBundle:Log:view_details.html.twig' %}

{% block logDetailsContext %}
    {{ parent() }}
    {% if constant('ICAP\\BlogBundle\\Event\\Log\\LogPostCreateEvent::ACTION') == log.action %}
        {% include 'ICAPBlogBundle:Log:log_details_post.html.twig' %}
    {% endif %}
{% endblock %}
```

You are of course encouraged to extends CoreBundle log base views, and just
override the part you want.
To do that you have at your disposal some block that you can customize, by
override or to add datas into:

 * logDetailsTitle: the title displayed at the top of the page (display
   `log_%actionName%_title` translation by default)
 * logDetailsSubtitle: display the same information than in the event list.
 * logDetailsContext: informations about the occured action


[index documentation][1]

[1]: ../index.md
