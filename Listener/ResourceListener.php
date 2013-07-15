<?php

namespace Innova\PathBundle\Listener;

use Claroline\CoreBundle\Library\Event\CopyResourceEvent;
use Claroline\CoreBundle\Library\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Event\CreateResourceEvent;
use Claroline\CoreBundle\Library\Event\OpenResourceEvent;
use Claroline\CoreBundle\Library\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Library\Event\DownloadResourceEvent;
/*use Claroline\ExampleBundle\Entity\Example;
use Claroline\ExampleBundle\Form\ExampleType;*/
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Claroline\CoreBundle\Library\Event\PluginOptionsEvent;

class ResourceListener extends ContainerAware
{
    //Fired once a user asks for the creation form.
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        //see the Form/ExampleType.php file. There is a required field for every resource.
        $form = $this->container->get('form.factory')->create(new ExampleType, new Example());
        //Use the following resource form.
        //Be carefull, the resourceType is case sensitive.
        //If you don't want to use the default form, feel free to create your own.
        //Make sure the submit route is
        //action="{{ path('claro_resource_create', {'resourceType':resourceType, 'parentInstanceId':'_resourceId'}) }}".
        //Anything else different won't work.
        //The '_resourceId' isn't a mistake, it's a placeholder wich will be replaced with js later on.

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig', array(
            'form' => $form->createView(),
            'resourceType' => 'claroline_example'
            )
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    //Fired once the creation form is submitted.
    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->container->get('form.factory')->create(new ExampleType, new Example());
        $form->handleRequest($request);

        if ($form->isValid()) {
            //gets the new resource.
            $example = $form->getData();
            //give it back to the event.
            $event->setResource($example);
            $event->stopPropagation();

            return;
        }

        //if the form is invalid, renders the form with its errors.
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'claroline_example'
            )
        );
        //give it back to the event.
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    //Fired once a resource is removed.
    public function onDelete(DeleteResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($event->getResource());
        $event->stopPropagation();
    }

    //Fired once a resource is copied.
    public function onCopy(CopyResourceEvent $event)
    {
        $resource = $event->getResource();
        $copy = new Example();
        $copy->setText($resource->getText());
        $copy->setName('copy');
        $event->setCopy($copy);
        $event->stopPropagation();
    }

    //Fired once a resource is downloaded.
    public function onDownload(DownloadResourceEvent $event)
    {
        $example = $event->getResource();
        //create new temporary file wich contains our text.
        $tmpfname = tempnam(sys_get_temp_dir(), 'clarotemp').".txt";
        //the name of the exported file will be its current name with the $tmpfname extension.
        file_put_contents($tmpfname, $example->getText());
        $event->setItem($tmpfname);
        $event->stopPropagation();
    }

    public function onOpen(OpenResourceEvent $event)
    {
        //Redirection to the controller.
        $route = $this->container
            ->get('router')
            ->generate('claro_example_open', array('exampleId' => $event->getResource()->getId()));
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    //This function is fired once the platform admins wants to edit your plugin options.
    //You can set theses options the way you want (database, config file, ...)
    //This event expect a response (usually containing a form)
    //Once the form is submitted, your function updating the options should redirect to the route 'claro_admin_plugins'
    public function onAdministrate(PluginOptionsEvent $event)
    {
        //you can use this function to display a form
    }
}
