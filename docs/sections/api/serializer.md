---
layout: default
title: Serializer
---

# Serializer


## SerializerProvider

**Class :** `Claroline\AppBundle\API\SerializerProvider`

**Dependency Injection ID :** `claroline.api.serializer`

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

#### serialize(_mixed_ $object, _array_ $options = [])

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


#### deserialize(_string_ $class, _array_ $data, _array_ $options = [])

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

**Namespace :** `MyVendor\MyBundle\Serializer\MySerializer`

**Dependency Injection Tag :** `claroline.api.serializer`

The `Serializer` is a service responsible of the serialization/deserialization of an Entity (and it's associations). 

> **Note**
> 
> All entities in the application don't require their own serializer. In most cases, there is only one `Serializer`
> for each objects exposed in the [JSON API](sections/json/index.md) (which includes a main Entity and it's associations).
>
> Some complex cases (like quiz resource) use multiple serializers for development purposes.
> In this cases, the additional serializers SHOULD NOT be registered in the `SerializerProvider`.


### Register into the `SerializerProvider`

In order to be able to access a `Serializer` from the [`SerializerProvider`](#serializerprovider), you need to register it.
For this, we use the symfony [Tagged Services](https://symfony.com/doc/current/service_container/tags.html).

```php
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Tag("claroline.api.serializer")
 */
class MySerializer
{
    // ...
}
```

### Methods

Here is the list of the standard serializer methods.

#### getClass()

Gets the full class name of the object managed by the serializer instance.

> **Note**
> 
> If none is provided we will use the type hint of the `$object` argument of `serialize()` method. 

##### Arguments

none

##### Returns

_(string)_ The full class of the managed object.

##### Example

```php
use MyVendor\MyBundle\Entity\MyObject;

class MySerializer
{
    public function getClass()
    {
        return 'MyVendor\MyBundle\Entity\MyObject';
    }
}
```


#### serialize(_mixed_ $object, _array_ $options = [])

##### Arguments

- `$object`  _(mixed)_ : the object to serialize.
- `$options` _(array)_ : a list of options for serialization.

##### Returns

_(array)_ A serializable array representation of `$object`.

##### Example

```php
use MyVendor\MyBundle\Entity\MyObject;

class MySerializer
{
    public function serialize(MyObject $object, array $options = [])
    {
        return [
            'name' => $object->getName(),
            // other serialized properties
        ];
    }
}
```


#### deserialize(_array_ $data, _mixed_ $object = null, _array_ $options = [])

##### Arguments

- `$data`    _(array)_ : the serialized data to use to populate the object.
- `$object`  _(mixed)_ : the entity instance to populate.
- `$options` _(array)_ : a list of options for deserialization.

##### Returns

_(mixed)_ The updated `$object` with `$data` props.

##### Example

```php
use MyVendor\MyBundle\Entity\MyObject;

class MySerializer
{
    public function deserialize(array $data, MyObject $object, array $options = [])
    {
        $object->setName($data['name']);
        
        // here goes the full deserialization process of the object
    }
}
```

### Options

You can alter the `Serializer` behavior by passing an array of options to the `serialize()` and `deserialize()`

## SerializerTrait

**Class :** `Claroline\AppBundle\API\Serializer\SerializerTrait`

You can use the `SerializerTrait` to have access to some serialization utilities.

### Methods

#### sipe(_string_ $prop, _string_ $setter, _array_ $data, _mixed_ $object)

An utility method (alias of `setIfPropertyExist`) to easily populate an object `$object` with
an array of data `$data`.

It uses string selectors like [lodash ones](https://lodash.com/docs/4.17.5#get) to retrieve data inside
the input array. Each `.` in the string will go one level deeper in the array. For example `meta.description` 
will returns `$data['meta']['description']`. 
If `meta` doesn't exist it will return `null` without error. 

This permits to avoid additional `isset()` checks in `deserialize()` implementations.

##### Arguments

- `$prop`    _(string)_ : the path to the prop inside the `$data` array. 
- `$setter`  _(string)_ : the name of the setter to use (it MUST be a public method of `$object`).
- `$data`    _(array)_  : the serialized data to use to populate the object.
- `$object`  _(mixed)_  : the object instance to populate.

##### Returns

_void_

##### Example

```php
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\User;

class MySerializer
{
    // use the trait in your serializer
    use SerializerTrait;
    
    public function deserialize(array $data, User $user = null)
    {
        // set the name of the entity
        $this->sipe('name', 'setName', $data, $object)
        
        // you can use lodash like selectors to deep select in $data
        $this->sipe('meta.description', 'setDescription', $data, $object)
    }
}
```

## Full `Serializer` example

```php
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use JMS\DiExtraBundle\Annotation as DI;
use MyVendor\MyBundle\Entity\MyObject;

/**
 * @DI\Service("my_plugin.serializer.my_object")
 * @DI\Tag("claroline.api.serializer")
 */
class MySerializer
{
    use SerializerTrait;
    
    public function getClass()
    {
        return 'MyVendor\MyBundle\Entity\MyObject';
    }

    public function getResources()
    {
        return '#/plugin/my/my-object';
    }
    
    public function serialize(MyObject $object, array $options = [])
    {
        $serialized = [];
        
        // TODO : implement
        
        return $serialized;
    }
    
    public function deserialize(array $data, MyObject $object = null, array $options = [])
    {
        // TODO : implement
        
        return $object;
    }
}
```


## Best practices

**You SHOULD avoid extra DB fetch.**

The `Serializer` are also used by [`Finder`](sections/api/finder.md), adding DB queries will slightly decreases 
performances. If you need to add extra data, you should add on `Options` to toggle it.


**YOU MUST NOT `persist()` or `flush()` the ObjectManager.**

Serialization is not only about persistence. We sometimes serialize/deserialize data for easier manipulations and
we don't want the underlying entities to be modified.
