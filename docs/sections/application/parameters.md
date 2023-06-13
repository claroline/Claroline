---
layout: default
title: Parameters
---

# Parameters

> This section only covers the API part of the parameters.
> To know how to declare and register UI for your tool, please see User Interface > Parameters

Platform parameters are stored in `/files/config/platform_options.json` and can be manipulated
with the `Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler` service.

Once you've injected the service, you can manipulate the platform parameters.

```php

// save the parameter `my_parameter` into platform_options.json
$this->config->setParameter('my_parameter', 'toto');

// check if the parameter `my_parameter` is set
$this->config->hasParameter('my_parameter') // return true

// get the current value for `my_parameter`
$this->config->getParameter('my_parameter') // return 'toto'

```

All methods of the PlatformConfigurationHandler accepts lodash like notation to search parameters.

```php

$this->config->setParameter('my_parameter', [
    'key' => 'toto',
]);

// access `key`
$this->config->getParameter('my_parameter.key') // return 'toto'

// check `key`
$this->config->hasParameter('my_parameter.key') // return true

// set `key`
$this->config->setParameter('my_parameter.key', 'tutu')

```


## Defining new parameters

Defining new parameters in the platform_options is considered deprecated and should be avoided whenever possible.
Instead, consider defining a `*Parameters` entity in your plugin to manage your parameters.


## Exposing parameters to the UI

Sometimes you will need to make your parameters available for the whole UI application.

You just have to subscribe to the `claroline_populate_client_config`.

```php
<?php

// my-plugin/Subscriber/ClientSubscriber.php

namespace MyVendor\MyPluginBundle\Subscriber;

use Claroline\CoreBundle\Event\GenericDataEvent;

class ClientSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'claroline_populate_client_config' => 'onPopulateConfig',
        ];
    }

    public function onPopulateConfig(GenericDataEvent $event)
    {
        $event->setResponse([
            // Retrieve `my_parameter` where it's stored (DB, platform_options, ...)
            // and expose it in the configuration array sent to the ui.
            // This MUST be a serializable structure.
            'my_parameter' => 'toto',
        ]);
    }
}

```

Don't forget to register the Subscriber in the DI.

```yml
# my-plugin/Resources/config/services/subscriber.yml

services:
    MyVendor\MyPluginBundle\Subscriber\ClientSubscriber:
        tags: [ kernel.event_subscriber ]
```
