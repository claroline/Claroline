---
layout: default
title: Editor
---

# Editors

Editors are multi-pages forms which opens in a fullscreen modal.
It is used to edit most of the app entities in the platform (ex. Workspaces, Resources, but also Users and Groups).

Editors are built on top of the Form components.

It connects to a standard form reducer generated with `makeFormReducer` (and therefore takes a `name` prop to specify to which store it connects, like form components).

## Standard pages

Editors come with some standard pages.
You can extend them to add your specific param to them.

### OverviewPage

### AppearancePage

### PermissionsPage

### HistoryPage

### ActionsPage

## Custom pages

In addition to the standard pages, you can add your own to fit the need of your entities.
