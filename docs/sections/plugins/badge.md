---
layout: default
title: Badge plugin
---

# Badge plugin

Claroline provides a badge system based on the [Open Badge][2] standard from
[Mozilla][3].

It uses the [rule system][4] also provided by Claroline.

## How it works

Badge can be issue to a user.

A user can ask for earning a badge.

A badge manager can issue him the badge if criteria is reunited.

Rules can be added to a badge.
This rules is used to determine if a badge can be awarded or not.

A badge can be automatically awarded, if configured that way.

## Create badges

To create a badge you just need to access the badge interface, in the administration panel or in the wokrspace one.
For managing badges on workspace you need to activate option first.


## Rule validation link

When on your profil, you can go see which badge you've got. When entering a badge page you can see why someone issued you it.
You see all the actions that lead to this awarding and for each action a link that lead to the resource, could be anything, on which the action was executed.
This link is generate with associated log datas.
Here is how link are generated.

You need to define a listener to the `badge-%action_name%-generate_validation_link` event.

Here [an example][5] of what you can do:

```php
use ;

public function onBagdeCreateValidationLink(BadgeCreateValidationLinkEvent $event)
    {
        $content = null;
        $log     = $event->getLog();

        switch($log->getAction())
        {
            case LogPostCreateEvent::ACTION:
                $logDetails = $event->getLog()->getDetails();
                $parameters = array(
                    'blogId'   => $logDetails['post']['blog'],
                    'postSlug' => $logDetails['post']['slug']
                );

                $url     = $this->router->generate('icap_blog_post_view', $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);
                $title   = $logDetails['post']['title'];
                $content = sprintf('<a href="%s" title="%s">%s</a>', $url, $title, $title);
                break;
        }

        $event->setContent($content);
        $event->stopPropagation();
    }
```

All this method need to return is some html content to represent the link to the associated resource, or any object you want.

If no listener is defined a default link will be generate with the resource node identifier of the log. If this one doesn't exist either no link will be generate.

[2]: http://openbadges.org/
[3]: http://www.mozilla.org/
[4]: rules.md
[5]: https://github.com/iCAPLyon1/BlogBundle/blob/master/Listener/BadgeListener.php#L33
