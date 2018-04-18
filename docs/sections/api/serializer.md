---
layout: default
title: Serializer
---

# Serializer


## Provider

**DI Tag : ** `claroline.api.serializer`

The `SerializerProvider` is responsible of the serialization/deserialization process of the 
 application Entities.

### Usages

The provider is standard service registered in the Symfony [ServiceContainer](https://symfony.com/doc/current/service_container.html).
 It can be retrieved from the `container` or directly injected inside another service.

```php
use Claroline\AppBundle\API\SerializerProvider;
// ...

/**
 * Injects SerializerProvider service.
 *
 * @DI\InjectParams({
 *     "serializerProvider" = @DI\Inject("claroline.api.serializer")
 * })
 *
 * @param SerializerProvider $serializerProvider
 */
public function injector(SerializerProvider $serializerProvider)
{
    // ...
}
```

### Methods

#### serialize($object, array $options = [])

Converts an Entity into a serializable (we use associative arrays) structure.

##### Arguments

##### Example

```php
use Claroline\CoreBundle\Entity\User;
user Claroline\AppBundle\API\Options;

// ...

$user = new User();

// gets the default serialized 
$serializedUser = $this->serializerProvider->serialize($user); 

// gets a minimal version of the user
$serializedUser = $this->serializerProvider->serialize($user, [Options::SERIALIZE_MINIMAL]);
```


#### deserialize($class, $data, array $options = [])

Converts a serializable structure into Entities.

##### Arguments

##### Example

```php

```


## Serializer instances
