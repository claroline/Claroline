---
layout: default
title: Testing
---

# Testing

### [PHPUnit](https://phpunit.de/)

Unit testing framework.

### [vfsStream](http://vfs.bovigo.org/)

Creates a virtual filesystem (useful for mocking the real one in unit tests).

# Create a test database

Claroline tests suite requires a database to be performed.

Make sure you have an up-to-date DB before going further :

```
$ bin/console claroline:update --env=test
```

# Run tests

To run the full test suite :

```
$ bin/phpunit --dont-report-useless-tests
```

To run a plugin test suite :

```
$ bin/phpunit src/plugin/MY_PLUGIN
```

Or a single test file :

```
$  bin/phpunit src/plugin/exo/Tests/Manager/Attempt/AnswerManagerTest.php
```
