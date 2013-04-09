[[Documentation index]][index_path]

[index_path]: ../index.md

#Resource plugin

## Database tables

You must create a migration class building your tables in the existing database.

The class must extend [*BundleMigration*](http://symfony.com/doc/2.0/bundles/DoctrineMigrationsBundle/index.html) and be placed in the *Migrations* folder. Its name must start with *Version* and end with a timestamp (YYYMMDDHHMMSS); e.g. *Version20121002000000.php*.

This class will be executed by the plateform when installing your plugin. It must contains two methods: up() and down(). They will be called to create or remove your tables to/from the database.

    /**
    *  BundleMigration is written on top of Doctrine\DBAL\Migrations\AbstractMigration
    *  and contains some helper methods.
    *  You can use the doctrine migration class as well (see the doctrine doc).
    */
    class Version20121002000000 extends BundleMigration
    {

        /**
        * Will be fired at the plugin installation.
        * @param \Doctrine\DBAL\Schema\Schema $schema
        */
        public function up(Schema $schema)
        {
            $this->createExampleTable($schema);
        }

        /**
        * Will be fired at the plugin uninstallation.
         * @param \Doctrine\DBAL\Schema\Schema $schema
         */
        public function down(Schema $schema)
        {
            $schema->dropTable('claro_example_text');
        }

        /**
        * Create the 'claro_example_text' table.
        * @param \Doctrine\DBAL\Schema\Schema $schema
        */
        public function createExampleTable(Schema $schema)
        {
            // Table creation
            $table = $schema->createTable('claro_example_text');
            // Add an auto increment id
            $this->addId($table);
            // Add a column
            $table->addColumn('text', 'text');
        }
    }


## Plugin configuration file
Your plugin must define its properties and the list of its resources in the *Resources/config/config.yml file*.
This file will be parsed by the plugin installator to install your plugin and create all your declared resources types in the database.

    plugin:
        # Properties of resources managed by your plugin
        # You can define as many resource types as you want in this file.
        resources:
            # "class" is the entity of your resource. This may be the entity of a existing
            # resource of the platform. This entity defines how the resource is stocked.
            # It may be usefull is your resource is a zip file with a particular structure.
            # In this case you can extend *Claroline\CoreBundle\Entity\Resource\File*.
          - class: Claroline\ExampleBundle\Entity\Example
            # Your resource type name
            name: claroline_example
            # Is it possible to navigate within your resource (does it have sub-resources ?)
            is_browsable: true
            # Do you want your resource to be exported as a part of a workspace model ?
            # Note: the default value of this parameter is "false"
            is_exportable: false
            # Icon for your resource.
            # They must be stored in the Resource/public/images/icon
            icon: res_text.png
            # Which are the actions we can fire from the resource manager.
            # Note that the resource manager will set some defaults actions
            #  (parameters, delete and download).
            actions:
                # The name of the action is the translation key that will be used to display
                #  the action in the list of available actions for your resource.
                #  The name will be passed to you by the Event manager.
              - name: open
                is_action_in_new_page: true

**/!\ it's a good practice to prefix your resources and widgets names to avoid possible conflicts with other plugins **

## Doctrine entities

Define your Doctrine entities in the Entity folder.

If your entity is a resource that must be recognized by the platform and manageable in the resource manager then you must extend the *Claroline\CoreBundle\Entity\Resource\AbstractResource* class.

    /**
    * @ORM\Entity
    * @ORM\Table(name="claro_example_text")
    */
    class Example extends AbstractResource
    {
        /**
        * @ORM\Column(type="string")
        */
        private $text;

        public function setText($text)
        {
            $this->text = $text;
        }

        public function getText()
        {
            return $this->text;
        }
    }

## Listener

The resource manager will trigger some events (Open, Delete...) on your resources. Your plugin must implements a listener to catch events that concern its resources and must apply appropriate action.

### Listener definition file

The definition of your listener must be placed in the *Resources/config/services/listeners.yml* file.

You declare in this file all events that you want to catch.

    services:
      claroline.listener.example_listener:
        # Class that implements the listener
        class: Claroline\ExampleBundle\Listener\ExampleListener
        # The Symfony Container will be given to the class
        calls:
          - [setContainer, ["@service_container"]]
        tags:
          - { name: kernel.event_listener, event: create_form_claroline_example, method: onCreateForm }
          - { name: kernel.event_listener, event: create_claroline_example, method: onCreate }
          - { name: kernel.event_listener, event: delete_claroline_example, method: onDelete }
          - { name: kernel.event_listener, event: download_claroline_example, method: onDownload }
          - { name: kernel.event_listener, event: copy_claroline_example, method: onCopy }
          - { name: kernel.event_listener, event: open_claroline_example, method: onOpen }
          - { name: kernel.event_listener, event: plugin_options_clarolineexample, method: onAdministrate }

Here is the list of events fired by the resource manager (lower case is forced here):

* create_form_*resourcetypename*
* create_*resourcetypename*
* delete_*resourcetypename*
* download_*resourcetypename*
* copy_*resourcetypename*
* open_*resourcetypename*
* *customaction*_*resourcetypename*

Where *resourcetypename* is the name of your resource (e.g. "example") and *customaction* is a custom action you defined earlier in the plugin configuration (e.g. "open").

This event is fired by the plugin managemement page:

* *plugin*_*options*_*myvendormyshortbundlename*

Where the shortbundle name is your bundle name without 'Bundle'.

#### note concerning the download

If your plugin don't catch the download event, a placeholder will be set in the archive.

### Listener implementation class

Define your listener class in the *Listener* folder.

    class ExampleListener extends ContainerAware
    {
      ...
      // Fired when a resource is removed.
      public function onDelete(DeleteResourceEvent $event)
      {
          $em = $this->container->get('doctrine.orm.entity_manager');
          foreach ($event->getResources() as $example) {
              $em->remove($example);
          }
          // Stop execution of further listeners
          $event->stopPropagation();
      }
      ...
    }

#### Forms

Please find the Symfony documentation here: http://symfony.com/doc/2.0/book/forms.html

##### Resources creation

Resources forms are a little be more complicated.

You can use the 'ClarolineCoreBundle:Resource:create_form.html.twig' as default form for your resource.

        ...
        //the form you defined with the symfony2 form component
        $form = $this->container
            ->get('form.factory')
            ->create(new ExampleType, new Example());
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:resource_form.html.twig', array(
            'form' => $form->createView(),
            /*you must add the attribute resourceType to the twig File.
            The Resource Manager need
            to know wich kind of resource is going to be added.*/
            'resourceType' => 'claroline_example'
            )
        );
        ...

**Warning**: don't forget the 'resourceType' attribute. Its value must be the 'name' field you defined in your config.yml file

If you want to write your own twig file, your form action must be:

    action="{{ path('claro_resource_create', {'resourceType': resourceType, 'parentId' '_resourceId'}) }}"

where resourceType is the 'name' field you defined in your config.yml file and _resourceId is a placeholder used
by the javascript manager.

###### Using existing forms & validations

This may be usefull if the *class* field you defined in your config file is an existing resource.
Let's assume you're using the Claroline\CoreBundle\Resource\File class.
Your listener should extends the Claroline FileListener.

    namespace MyVender\MyBundle\Listener;

    use Claroline\CoreBundle\Listener\Resource\FileListener;

    class MyListener extends FileListener {
     ...
    }

Then you must override the creationForm method. If you don't know wich method
to override, you can check wich method is called on the create_form_xxx event
in the config files.

    use Claroline\CoreBundle\Form\FileType;
    use Claroline\CoreBundle\Entity\Resource\File;
    use Claroline\CoreBundle\Library\Event\CreateFormResourceEvent;
    use Claroline\CoreBundle\Listener\Resource\FileListener;
    ...

    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new FileType, new File());
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:resource_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'myresourcetype'
            )
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

This function will create a File whose ResourceType is MyResource.
Because you extended the FileListener, you don't have to implement
the create_xxx event.

## Translations

* resource.xx.yml

We use lower case for every translation keys.
You must translate your resource type name in this file.

    example: example

Where example is the name you defined in your config file.
It's located in Resources/config/translations

## Resources

You can consider every class extending AbstractResource as a way to stock
your resources datas.
This AbstractResource class some very important relations.

### ResourceType

This entity job is to stock important attributes wich will differ depending on
the ResourceType.
Theses attributes are:

* isBrowsable;
* isVisible;

These attributes are defined in the resource section in your config file.

It also has relations to the customaction and the plugin tables.

Once you created your basic entity and filled the field you defined yourself,
you can ask the resource manager to set every needed relations and persist it.

Hopefully, this is very easy to do:

At first, get the claroline.resource.manager service.

     $creator = $this->get('claroline.resource.manager');

Then you must use the create() method.

Here is the method signature:

     public function create(AbstractResource $resource, $parentId, $resourceType, User $user = null)

### Keeping the context

AbstractResource has a mandatory relation to the AbstractWorkspace table.
The Workspace indicate the context in wich your resource was placed.

You can find the workspace using

    $workspace = $resource->getWorkspace();

Then your response must extends the workspace layout.

    {% extends "ClarolineCoreBundle:Workspace:layout.html.twig" %}

This layout requires the **workspace** parameter. Its value must be the $workspace
you got from the instance.

### Removing a Resource Plugin

Plugins can be managed with theses commands:

    claroline:plugin:install VendorName BundleName
    claroline:plugin:uninstall VendorName BundleName

If you're removing a plugin whose resource class is defined by the claroline platform,
Resources having the type managed by the plugin will stay under their 'super type'.
Otherwise, they'll be removed.

### Editing a resource

There is no predefined event for this action.
If you want to implement it, you must create some custom actions (see plugin configuration file).

## Right management (They're going to change soon)

### Database

ResourceRights are stored in the entity Resource\ResourceRights.
This table has a reliation to a role, a resource and a workspace.
It has several booleans defining the current permissions:
canCopy, canDeleten canEdit, canOpen, canExport.

It also has a N-N relation with the ResourceType table. This relation indicate with
ResourceType can be created has children in the current Resource (if it's a directory).

### Voter

The ResourceVoter will grant permissions to a user to execute an action.
He's called when the method "$this->get('security.context')->isGranted($action, $object);" is fired.
Possible $actions are 'MOVE', 'COPY', 'DELETE', 'EXPORT', 'CREATE', 'EDIT', 'OPEN'.
The $object parameters is a Library\Resource\ResourceCollection class
This object can take an array of parameters (setParameters) where keys are 'parent' and 'type'.

'parent' is the parent entity in wich the action is done.
'type' the the resource type of the soon to be created resource.

Theses parameters may be required in some case ('CREATE', 'MOVE', 'COPY').

CREATE => type is required
MOVE => parent is required
COPY => parent is required

### Creation

Rights are defined for the first time at the workspace root at the workspace creation.
When a resource is created, the parent rights are copied to the children rights (same when a resource is moved
or copied).

## Resource export.

### @see forum bundle. No doc yet (import/export_template events). These events need to be caught
if the resource is exportable.