---
layout: default
title: Serializer
---

# Serializer


## Provider

**DI Tag :** `claroline.api.serializer`

The `SerializerProvider` is responsible of the serialization/deserialization process of the 
 application Entities.

### Usages

The provider is a standard service registered in the Symfony [ServiceContainer](https://symfony.com/doc/current/service_container.html).
 It can be retrieved from the `container` or directly injected inside another service.

```php
use Claroline\AppBundle\API\SerializerProvider;
use JMS\DiExtraBundle\Annotation as DI;

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

Once you've injected or retrieved the provider, you have access to the following methods :

#### serialize($object, array $options = [])

Converts an Entity into a serializable (we use associative arrays) structure.

##### Arguments

- `$object`  _(mixed)_ : the entity to serialize. The provider will retrieve the correct serializer instance by inspecting the class of `$object`.
- `$options` _(array)_ : a list of options for serialization.

##### Returns

_(array)_ A serializable array representation of `$object`.

##### Example

```php
use Claroline\CoreBundle\Entity\User;
use Claroline\AppBundle\API\Options;

// ...

$user = new User();

// gets the default serialized version of the user
$serializedUser = $this->serializerProvider->serialize($user); 

// adds an option to get the minimal version of the user
$serializedUser = $this->serializerProvider->serialize($user, [Options::SERIALIZE_MINIMAL]);
```


#### deserialize($class, $data, array $options = [])

Converts a serializable structure into the Entity `$class`.

If `$data` contains an `id`, the provider will try to fetch it from the DB (you can avoid this behavior with
the option `Options::NO_FETCH`) otherwise it will initialize a new object.

##### Arguments

- `$class`   _(string)_ : the full class name (including namespace) of the target entity.
- `$data`    _(mixed)_  : the serialized data to use to populate the object.
- `$options` _(array)_  : a list of options for deserialization.

##### Returns

_(mixed)_ An instance of `$class` filled with the content of `$data`.

##### Example

```php
use Claroline\CoreBundle\Entity\User;

// ...

$serializedUser = [
    'firstName' => 'John',
    'lastName' => 'Doe'
    'email' => 'john.doe@claroline.com'
];

// get a User entity from the serialized data
$user = $this->serializerProvider->deserialize('Claroline\CoreBundle\Entity\User', $serializedUser); 

echo $user instanceof User; // true
echo $user->getFirstName(); // John
```


## Serializer instances
