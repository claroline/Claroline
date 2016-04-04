[[Documentation index]][1]

#Widget plugin

## Plugin configuration file

Your plugin must define its properties and the list of its widgets in the
*Resources/config/config.yml file*.

This file will be parsed by the plugin installator to install your plugin and
create all your declared widgets in the database.

```yml
plugin:
    # Widgets declared by your plugin.
    widgets:
    # Each widget requires a name.
     - name: claroline_exemple
    # Set this to true if the widget is configurable
       is_configurable: true
    # You can set an icon for your widget. The icon must be in your public/images/icons folder.
       icon: something.jpeg
     - name: claroline_theanswertolifeuniverseandeverything
       is_configurable: false
```

## Translations

* widget.xx.yml

We use lower case for every translation keys.

Create the *widget* file in your Resources/translations folder.
You can translate your widget names here.

```yml
mywidgetname: mytranslation
```

Where mywidgetname is the name you defined in your config file.

### Configuring the widget

Each widget can be instanciated any number of time.
You can create your own configuration table for a widget and
use its datas to display different informations depending on the context.

The kernel will fire the *widget_yourwidgetname_configuration* each time the configuration form is asked.
First you must define a listener to catch the event.

```php
/**
 * @DI\Observe("widget_mywidget_configuration")
 */
public function onConfig(ConfigureWidgetEvent $event)
```

You can retrieve the instance wich is asking to be configured using

```php
$instance = $event->getInstance();
```

You'll need to return the configuration form html to the kernel.

```php
...
$content = $this->container->get('templating')->render(
    'MyVendorMyBundle::my_form.html.twig', array(
    'form' => $form->createView(),
    'instance' => $instance
    )
);

$event->setContent($content);
$event->stopPropagation();
```

**your_form.html.twig:**

```html+jinja

{% form_theme form 'ClarolineCoreBundle::form_theme.html.twig' %}
<form class="form-horizontal"
      action="{{ path('my_submission_route', {'instance': instance.getId()}) }}"
      method="post" {{ form_enctype(form) }}
>
    <div class="panel-body">
        {{ form_widget(form) }}
    </div>
    <div class="panel-footer">
    <button type="submit" class="btn btn-primary">{{ 'ok'|trans({}, 'platform') }}</button>
        <a href="#">
            <button type="button" class="btn btn-default">{{ 'cancel'|trans({}, 'platform') }}</button>
        </a>
    </div>
</form>
```

The cancel button must have **claro-widget-form-cancel** for ajax form's
injection.

**Controller**

Everything is done with ajax for now.

You should write a controller able to handle your submission route. This
controller should either persist the changes and return a 204 response or
return the form with its validation errors.

### Displaying the widget

The kernel will fire the *widget_yourwidgetname* event each time the widget is
rendered. You must define a listener to catch the event.

```php
/**
 * @DI\Observe("widget_mywidget")
 *
 * @param DisplayWidgetEvent $event
 */
public function onDisplay(DisplayWidgetEvent $event)
{
    $event->setContent('some content);
    $event->stopPropagation();
}
```

[[Documentation index]][1]

[1]: ../../index.md
