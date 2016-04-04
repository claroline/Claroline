[[Documentation index]][1]

Resources
=========

Overview
--------

Resources are items wich can be manipulated by the resource manager.
They include the data structure and content of what you would call a file
explorer.

Entities
--------

Resources are defined by a 3 different entities:

*You can see these relations in claronext-uml.dia*

### Abstract Resource

AbstractResource is a doctrine mapped super class (#ref to doc).
Each resource defined by a plugin or the claroline core must extends this class.
It has a mandatory relation to the ResourceNode entity. You can consider this
entity and its children as a way to store resources datas.

### Resource Node

The ResourceNode entity contains the directory structure of the data tree and
its context.
For instance, this is were you'll find a resource children
(if it's a directory), name, creator, creation date,... but no actual content.

You can use the ResourceNodeRepository to retrive resource. You can find the
availables methods at Repository/ResourceNodeRepository.
If you need the actual content, you will have to use the *getResourceFromNode*
method from the ResourceManager

```php
$res = $this->get('claroline.resource.manager')->getResourceFromNode($node);
```

### Resource Type

The ResourceType entity stores the list of resources wich can be created
(ie File, Directory, Exercice,... ).

### Permissions

Permissions are stored as integer in the ResourceRight entity. This entity is
the join between a ResourceNode and a Role. These permissions can be decoded
with the MaskDecoder entity wich is used by the right manager.

If you need to test a permission, use the isGranted function.
The resource voter currently only works with ResourceCollection objects.
To test a basic permission, use:

```php
$collection = new Claroline\CoreBundle\Library\Security\Collection\ResourceCollection($node);
$securityContext->isGranted($permission, $collection);
```

In this case, **$permission** is a string for the permission name.
Some are defined by default in the database for each ResourceType:

- open
- copy
- delete
- export
- edit

Some are handled by the voter because they're a combination of the following
permissions. They require some additional parameters.
Theses attributes can be set by using:

```php
ResourceCollection::setAttributes($array);
```

or:

```php
ResourceCollection::addAttribute($key, $value);
```

- create
- move
- copy

Create requires setAttribute(array('type' => $resourceType)) where $resourceType
is the name of the created Resource.

Copy and move require $collection->addAttribute('parent', $parent) where parent
is the new parent node.

And some can be specific for a ResourceType.

- moderate (for a forum)
- post (for a forum)


[[Documentation index]][1]

[1]:  ../index.md
