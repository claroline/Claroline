[[Documentation index]][1]

# Resource actions plugin

## Plugin configuration file

You can define resource actions that can be show on each resource type. You can add to the following file  *Resources/config/config.yml file* those actions.

This file will be parsed by the plugin installator to install your plugin and create all your declare resource actions in the database.

```yml
plugin:
     # Properties of resources actions
    resource_actions:
        # You can define as many resource actions as you want in this file
      - name: actionname1
      - name: actionname2
```

## Listener implementation class 

Define your listener class in the *Listener* folder of your plugin.

```php
/**
 * @DI\Observe("resource_action_[actionName]")
 *
 * @param CustomActionResourceEvent $event
 */
public function on[actionName]Action(CustomActionResourceEvent $event)
{
    $activity = $event->getResource();
    $content = ...
    $response = new Response($content);
    $event->setResponse($response);
    $event->stopPropagation();
}
```

Replace \[actionName\] by the name of the action you define in your *Resource/config/config.yml*.

## Translation

* resource.xx.yml

We use lower case for every translation keys.
You must translate your resource actions names in this file.

```yml
[actionName]: 'My first action'
```

Replace \[actionName\] by the name of the action. 

[[Documentation index]][1]

[1]: ../../index.md