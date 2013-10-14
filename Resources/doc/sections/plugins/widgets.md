[[Documentation index]][index_path]

#Widget plugin

## Plugin configuration file

Your plugin must define its properties and the list of its widgets in the *Resources/config/config.yml file*.
This file will be parsed by the plugin installator to install your plugin and create all your declared widgets in the database.

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

## Translations

* widget.xx.yml

We use lower case for every translation keys.

Create the *widget* file in your Resources/translations folder.
You can translate your widget names here.

    mywidgetname: mytranslation

Where mywidgetname is the name you defined in your config file.

### Configuring the widget

Each widget can be instanciated any number of time.
You can create your own configuration table for a widget and
use its datas to display different informations depending on the context.

#### Workspace

The kernel will fire the *widget_**your_widget_name**_configuration each time the configuration form is asked.
First you must define a listener to catch the event.

**listeners.yml file:**

     - { name: kernel.event_listener, event: widget_claroline_my_widget_configuration, method: onConfigure 

You can retrieve the instance wich is asking to be configured using

    $instance = $event->getInstance();

You'll need to return the configuration form html to the kernel.

    ...
    $content = $this->container->get('templating')->render(
        'MyVendorMyBundle::my_form.html.twig', array(
        'form' => $form->createView(),
        'rssConfig' => $config
        )
    );

    $event->setContent($content);
    $event->stopPropagation();

**your_form.html.twig:**

    <script type="text/javascript" src='{{ asset('bundles/frontend/jquery/jquery-1.7.1.min.js') }}'></script>
    {% render controller('ClarolineCoreBundle:ResourceType:initPicker') %}
    {{ tinymce_init() }}

    {% form_theme form 'ClarolineCoreBundle::form_theme.html.twig' %}
    <form class="form-horizontal"
          action="{{ path('claro_simple_text_update_config', {'widget': config.getId()}) }}"
          method="post" {{ form_enctype(form) }}
    >
        <div class="panel-body">
            {{ form_widget(form) }}
        </div>
        <div class="panel-footer">
        <button type="submit" class="btn btn-primary">{{ 'ok'|trans({}, 'platform') }}</button>
            <a href="{{ cancelUrl }}">
                <button type="button" class="btn btn-default">{{ 'cancel'|trans({}, 'platform') }}</button>
            </a>
        </div>
    </form>

The cancel button must have **claro-widget-form-cancel** for ajax form's injection
The action of the form should redirect to one of your controller which will persist the modification to the configuration

**controller class:**

You'll have to take care of the redirection once the change are persisted.

     return new RedirectResponse($this->get('claroline.manager.widget_manager')->getRedirectRoute($widgetInstance);

### Displaying the widget

Widgets can be displayed at 2 differents key pages:

* Desktop home
* Workspace home

Every time a user is loading one of these page, the list of registered widgets will be loaded.
Every time the platform wants to display a widget, the event is fired

    widget_*widget_name*_*workspace|dekstop*

Where

* widget is a prefix
* widgetName is the name of your widget defined in the config file.
* the last word is either workspace or desktop depending on where the widget is displayed

### Catching the event

Define a listener in your listeners.yml file

    myvendor.listener.mybundle_widget:
      class: ...
      tags:
        - { name: kernel.event_listener, event: widget_widgetname_workspace, method: onWorkspaceDisplay }

### Listener implementation

#### Workspace

Simply set a string in the $event->setContent() method.

    use Claroline\CoreBundle\Event\DisplayWidgetEvent;
    ...
    function onWorkspaceDisplay(DisplayWidgetEvent $event)
    {
        $event->setContent('someContent');
    }

If your widget is configurable, you can find the context using $event->getWorkspace()->getId()
Then you must know if the admin wants the default config to be used. You can know it using

    $boolean = $this->container->get('claroline.manager.widget_manager')->isWorkspaceDefaultConfig($widget->getId(), $event->getWorkspace()->getId());
    $boolean = $this->container->get('claroline.manager.widget_manager')->isDesktopDefaultConfig($widget->getId(), $user->getId());

###### Keeping the context

You can retrieve the workspace using
    $event->getWorkspace();


[index_path]: ../../index.md