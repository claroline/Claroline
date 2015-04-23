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

### Be redirected to a new page
Here is an exemple, if you want to be redirected to a new page.
```php
/**
 * @DI\Observe("resource_action_actionName")
 *
 * @param CustomActionResourceEvent $event
 */
public function onActionNameAction(CustomActionResourceEvent $event)
{
    $content = $this->templatingEngine->render('SomeBundle:SomeDirectory:someTwig');
    $event->setResponse(new Response($content));
    $event->stopPropagation();
}
```
Replace actionName by the name of the action you define in your *Resource/config/config.yml*.

### Form set up
#### Create
If you want to create a form popup, you must render a modal form.

```php
/**
 * @DI\Observe("resource_action_actionName")
 *
 * @param CustomActionResourceEvent $event
 */
public function onActionNameAction(CustomActionResourceEvent $event)
{
    $content = $this->templatingEngine->render('SomeBundle:SomeDirectory:form.html.twig', array('form' => $form);
    $event->setResponse(new Response($content));
    $event->stopPropagation();
}
```

```html
<-- Resources/views/SomeDirectory/form.html.twig ->
<div class="modal-dialog">
    <form role="form" novalidate="novalidate"
        action="{{ path('someRoute') }}"
        method="post" class="modal-content" {{ form_enctype(form) }}>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4 class="modal-title">Your title</h4>
        </div>
        <div class="modal-body">
            {{ form_errors(form) }}
            {{ form_widget(form) }}
            {{ form_rest(form) }}
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{ 'cancel'|trans({}, 'platform') }}</button>
            <input type="submit" class="btn btn-primary" value="{{ 'ok'|trans({}, 'platform') }}">
        </div>
    </form>
</div>
```

If you want to hide the form, you have to use the *cancel* button as shown above.
Same for the submit button.

#### Submit
If the form is valid, you have to return a JsonResponse.

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