[[Documentation index]][1]

# Resource actions plugin

## Plugin configuration file

You can define resource actions that can be show on each resource type. You can add to the following file  *Resources/config/config.yml file* those actions.

This file will be parsed by the plugin installator to install your plugin and create all your declare resource actions in the database.

```yml
plugin:
     # Properties of resources actions
    resource_actions:
        # You can define as many resource actions as you want in this file
      - name: actionname1
        is_form: true (optional)
      - name: actionname2
        is_form: false
```

First, define the *name* of the action you'll want to add to your resource.
Then define the *is_form* parameter. If it's set to true, a popup form will show when you click an the action. If not, you'll be redirect to a new page you define in your Listener.

## Listener implementation class
Define your listener class in the *Listener* folder of your plugin.
As describe above, there are two possibles types of action when you click on the resource action, be redirected to a new page or create a form popup.

Replace actionName by the name of the action you define in your *Resource/config/config.yml*.

## Translation

* resource.xx.yml

We use lower case for every translation keys.
You must translate your resource actions names in this file.

```yml
actionName: 'My first action'
```

Replace actionName by the name of the action.

[[Documentation index]][1]

[1]: ../../index.md
