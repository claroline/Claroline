---
layout: default
title: Core concepts
---

# Core concepts

## Introduction

**Claroline** is built on top of the Symfony framework (version 4.x). It also
relies on libraries and frameworks commonly associated with Symfony, such as
Doctrine and Twig. This documentation doesn't cover the usage of those
dependencies. If you have any trouble with them, please refer to their official
documentation :

- [Symfony][2]
- [Doctrine][3]
- [Twig][4]

Sections below, and more generally this documentation, are intended to
highlight differences between Claroline and the standard Symfony distribution,
as well as the particular technical choices that have been made so far.


## Bundles

The bundles are structured in the same way than any Symfony bundle. 
You will find in most of the bundles the usual *Controller*, *Entity*,
*Resources*, etc. directories.


## Database

Claroline use the Doctrine ORM for database interactions. It allows both a
simpler development process and a portability across the major RDBMS (see the
DBAL documentation for a list of [supported drivers][5]).


### Data fixtures

A useful feature is the ability to load automatically some data sets in
the database, whether to provide some demo samples with a bundle or to have
data to work with in an automatic test. In Claroline, this result is achieved
using the Doctrine [Data Fixtures][8] library. A fixture is simply a class
implementing a `load` method, to which an instance of the entity manager is
passed by Doctrine when the fixture is executed. In that method, you can
therefore use the manager to persist any needed data. Example :

```php

namespace Foo\BarBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Foo\BarBundle\Entity\FooEntity;

class FooFixture extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $foo = new FooEntity();
        $foo->setName('bar');
        $manager->persist($foo);
        $manager->flush();
    }
}

```

The data fixtures loading is also part of the installation of every bundle in
Claroline. To benefit from this feature, simply put your fixture classes in
a *Installation\DataFixtures* directory :

<pre>
bundle
+-- Installation
    +-- DataFixtures
        +-- FooFixture.php
</pre>

Using those fixtures in a test requires to instantiate the fixture class and
provide its dependencies manually. This will be covered later in this
documentation.

[2]:  http://symfony.com/doc/current/index.html
[3]:  http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/index.html
[4]:  http://twig.sensiolabs.org/documentation
[5]:  http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#driver
[6]:  http://docs.doctrine-project.org/en/2.0.x/reference/tools.html#database-schema-generation
[7]:  http://docs.doctrine-project.org/projects/doctrine-migrations/en/latest/toc.html
[8]:  https://github.com/doctrine/data-fixtures
