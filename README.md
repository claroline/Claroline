# AgendaBundle
## Synopsis
This is a plugin for the platform Claroline.
This plugin adds an agenda and two widgets which list the future events of the user and the tasks that are not done.

## Installation
This bundle requires claroline/core-bundle on version ~5.0.

`composer require claroline/agenda-bundle "dev-master"`

## Extension
You can use the event entity for your extend bundle so that the event is also shown in the desktop agenda. If you don't want that the event is editable in the desktop agenda, you can set the isEditable attribute to false so that **ANYONE** could modify that event.