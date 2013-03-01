[[Documentation index]][index_path]

[index_path]: ../index.md

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

You can create your own configuration table for a widget and
use its datas to display different informations depending on the context.

#### Workspace

The Claroline kernel works using a default configuration (defined by the admin) and a specific configuration for each different widget.
The kernel will fire the *widget_**your_widget_name**_configuration_workspace* each time the configuration form is asked.
First you must define a listener to catch the event.

**listeners.yml file:**

     - { name: kernel.event_listener, event: widget_claroline_my_widget_configuration_workspace, method: onWorkspaceConfigure }

**listener class:**

    public function onWorkspaceConfigure(ConfigureWidgetWorkspaceEvent $event)
    {
        ...
    }

You can know for wich workspace the configuration change is asked using

    $workspace = $event->getWorkspace();

If the $workspace is null, it means it's a change for the default configuration.

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

The action of the form should redirect to one of your controller wich will persist the modification to the configuration.
You'll have to take care of the redirection once the change are persisted.
You'll want to redirect to these routes depending on the context:

    if ($config->getWorkspace() != null) {
        $url = $this->generateUrl(
            'claro_workspace_open',
            array('workspaceId' => $config->getWorkspace()->getId())
        );

        return new RedirectResponse($url);
    }

    if ($isDefault) {
        return new RedirectResponse($this->generateUrl('claro_admin_widgets'));
    }

    return new RedirectResponse($this->generateUrl('claro_desktop_open'));

*In this snippet, the $config var is an entity of the plugin widget configuration table.*

#### Desktop

The kernel use the same system for the desktop widgets where the different workspace are replaced by the users of the platform.
You can catch the event using

**listeners.yml file:**

     - { name: kernel.event_listener, event: widget_claroline_my_widget_configuration_Desktop, method: onDesktopConfigure }

**listener class:**

    public function onDesktopConfigure(ConfigureWidgetDesktopEvent $event)
    {
        ...
    }

You can know for wich user the configuration change is asked using

    $user = $event->getUser();

If the $user is null, it means it's a change for the default configuration.


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

    use Claroline\CoreBundle\Library\Event\DisplayWidgetEvent;
    ...
    function onWorkspaceDisplay(DisplayWidgetEvent $event)
    {
        $event->setContent('someContent');
    }

If your widget is configurable, you can find the context using $event->getWorkspace()->getId()
Then you must know if the admin wants the default config to be used. You can know it using

    $boolean = $this->container->get('claroline.widget.manager')->isWorkspaceDefaultConfig($widget->getId(), $event->getWorkspace()->getId());
    $boolean = $this->container->get('claroline.widget.manager')->isDesktopDefaultConfig($widget->getId(), $user->getId());

###### Keeping the context

You can retrieve the workspace using
    $event->getWorkspace();
