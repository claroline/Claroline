Event Tracking
============

Event Tracking is a system that you can use to record user interaction with platform.

This is accomplished by attaching the appropriate event to the particular action you want to track.
When used this way, all user activity on such actions is calculated and displayed as Events in the Tracking reporting interface.

Event Tracking system can collect four major event categories:

 * User events (user create, update, delete etc.)
 * Group events
 * Resources events
 * Roles events

Advanced features
-------------

In this section we cover what we call “advanced features” of the event tracking process.
These features include:
 * Not repeatable log
 * Mechanisms for displaying plugins’ logs


### Not repeatable log ###

Some events such as resourceRead need to be filtered before storing them to the database
in order to avoid information redundancy. By information redundancy we mean that the same
event even if it occurs many times during a specific period of time it only has to be stored
once.

E.g.: Taking the case of a blog for example. In a blog it is quite possible that a user navigates
though it’s different pages, posts, comments etc. Every time the user navigates a “read Blog”
event occurs (since he is loading another page / item of the same blog).


It is clear that storing all these occurrences is pointless since we only need one to tell us that
a user started reading the resource. For that reason, we choose to store only the first of
these occurrences then wait some time until we store again the same info. The “delay” time
is configurable in the parameters.yml file.

The events that are defined as “Not repeatable” are the following:
 * ResourceRead
 * WorkspaceToolRead

The mechanism behind that functionality does NOT use any database interaction. Instead,
last event occurrence is stored in client’s http session having as value a timestamp (when the
event first occurred).


If this behavior needs to be applied in other events as well, then the created event MUST
implement the NotRepeatableLog interface. The NotRepeatableLog interface requires the
definition of a method called getLogSignature() which generates a unique identifier for this
event’s occurrences. For any resource the unique identifier would has the following form:resource_read_abstractResourceID. For example in the case of a Blog whose id is 42 the
generated signature would be: resource_read_42.


### Mechanisms for displaying plugins’ logs ###

When one creates a new plugin, among other functions and listeners he also needs to define
listeners and functions for displaying plugin’s special logs. In a blog plugin for example,
listeners and functions have to be defined in order to render properly the logs of post
creation, comment publication etc.


In details the listeners that need to be defined are the following:
- create_log_list_item_resource_type (e.g. create_log_list_item_icapreferencebank)
- create_log_details_resource_type (e.g. create_log_details_icapreferencebank)
As well as the functions called by these listeners.


The first listener is responsible for rendering the event in an appropriate form for the events
list (short presentation of the event). An example of this function is given below:

```php
public function onCreateLogListItem(LogCreateDelegateViewEvent $event)
{
    $content = $this->container->get('templating')->render(
        'ICAPReferenceBundle::log_list_item.html.twig',
        array('log' => $event->getLog())
    );
    $event->setResponseContent($content);
    $event->stopPropagation();
}
```

The example shows that along with the listener and the function a (twig) view is also
required. In the given example this view is the ‘log_list_item.html.twig’, responsible to display
the event in a short form. Below is given the code of this view:

```php
{% set doer %}
    {% include 'ClarolineCoreBundle:Log:view_list_item_doer.html.twig' %}
    // Renders the doer’s info (defined by core)
{% endset %}

{% set reference %}
    {% include 'ICAPReferenceBundle::log_list_item_reference.html.twig' %}
    // Renders the plugin’s object info (defined by plugin itself)
{% endset %}

{% set resource %}
    {% include 'ClarolineCoreBundle:Log:view_list_item_resource.html.twig' %}
    // Renders resource’s info (defined by core)
{% endset %}

// Follows code for translated representation of the text
{% if log.getChildType == 'icap_reference' %}
    {% if log.getChildAction == 'child_action_create' %}
        {{ 'referenceCreate'|trans({'%doer%': doer, '%reference%': reference, '%resource%': resource})|raw }}
    {% elseif log.getChildAction == 'child_action_delete' %}
        {{ 'referenceDelete'|trans({'%doer%': doer, '%reference%': reference, '%resource%': resource})|raw }}
    {% elseif log.getChildAction == 'child_action_update' %}
        {{ 'referenceUpdate'|trans({'%doer%': doer, '%reference%': reference, '%resource%': resource})|raw }}
    {% else %}
        no default text
    {% endif %}
{% else %}
    no default text
{% endif %}
```

The second listener displays the event in details. Hence, both its function and its view are
more complex.

```php
public function onCreateLogDetails(LogCreateDelegateViewEvent $event)
{
    $content = $this->container->get('templating')->render(
        'ICAPReferenceBundle::log_details.html.twig',
        array(
            'log' => $event->getLog(),
            'listItemView' => $this->container->get('templating')->render(
                'ICAPReferenceBundle::log_list_item.html.twig',
                array('log' => $event->getLog())
            )
        )
    );
    $event->setResponseContent($content);
    $event->stopPropagation();
}
```
The associated view is the ‘log_details.html.twig’ and its code is given below:

```php
{% extends 'ClarolineCoreBundle:Log:view_details.html.twig' %}

// Inheritance of a view that lies in core
{% block logDetailsTitle %}
    {% if log.getChildType == 'icap_reference' %}
        {% if log.getChildAction == 'child_action_create' %}
            {{ 'referenceCreateTitle'|trans }}
        {% elseif log.getChildAction == 'child_action_delete' %}
            {{ 'referenceDeleteTitle'|trans }}
        {% elseif log.getChildAction == 'child_action_update' %}
            {{ 'referenceUpdateTitle'|trans }}
        {% else %}
            no default text
        {% endif %}
    {% else %}
        no default text
    {% endif %}
{% endblock %}
{% block logDetailsContext %}
    {{ parent() }}
    {% if log.getChildType == 'icap_reference' %}
        {% include 'ICAPReferenceBundle::log_details_reference.html.twig' %}
        // Displays details of plugin’s object (Defined by plugin)
    {% endif %}
{% endblock %}
```

###Create its own event###

If you want you can create your own event class.
It's easy, you just need to keep in mind that database informations will remain the same.
You just create a wrapper of an existing event, but with a custom action.

For example l'ts just say that...

----------

Return to :

- [core documentation][1]
- [index documentation][2]


[1]: core.md
[2]: ../index.md