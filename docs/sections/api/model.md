---
layout: default
title: Model
---

# Model

We use [Doctrine entities](https://symfony.com/doc/current/doctrine.html) to manage our model.

## Syncing 

Each time you modify an entity, you'll need to resync your DB to reflect the changes.
This is down through the Doctrine migrations. See [Database migrations](Claroline/sections/dev/migrations) from more information.

> ATTENTION : never use the `bin/console doctrine:schema:update --force` command to sync your database.

## Best practices

- Table SHOULD be named with the following convention : `vendor_plugin_entity` (ex. claro_core_workspace, claro_community_team).
