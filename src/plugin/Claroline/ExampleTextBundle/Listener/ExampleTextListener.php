<?php

namespace Claroline\ExampleTextBundle\Listener;

use Claroline\CoreBundle\Library\Resource\Event\CopyResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\CreateResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\ExportResourceEvent;
use Claroline\ExampleTextBundle\Entity\ExampleText;
use Claroline\ExampleTextBundle\Form\ExampleTextType;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Claroline\CoreBundle\Library\Plugin\Event\PluginOptionsEvent;

class ExampleTextListener extends ContainerAware
{
    //Fired once a user asks for the creation form.
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        //see the Form/ExampleTextType.php file. There is a required field for every resource.
        $form = $this->container->get('form.factory')->create(new ExampleTextType, new ExampleText());
        //Use the following resource form.
        //Be carefull, the resourceType is case sensitive.
        //If you don't want to use the default form, feel free to create your own.
        //Make sure the submit route is
        //action="{{ path('claro_resource_create', {'resourceType':resourceType, 'parentInstanceId':'_instanceId'}) }}".
        //Anything else different won't work.
        //The '_instanceId' isn't a mistake, it's a placeholder wich will be replaced with js later on.

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:resource_form.html.twig', array(
            'form' => $form->createView(),
            'resourceType' => 'ExampleText'
            )
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    //Fired once the creation form is submitted.
    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->container->get('form.factory')->create(new ExampleTextType, new ExampleText());
        $form->bindRequest($request);

        if ($form->isValid()) {
            //gets the new resource.
            $exampleText = $form->getData();
            //give it back to the event.
            $event->setResource($exampleText);
            $event->stopPropagation();
            return;
        }

        //if the form is invalid, renders the form with its errors.
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:resource_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'forum'
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

        foreach ($event->getResources() as $exampleText) {
            $em->remove($exampleText);
            //not sure if the flush is needed
            $em->flush();
        }

        $event->stopPropagation();
    }

    //Fired once a resource is copied.
    public function onCopy(CopyResourceEvent $event)
    {
        $resource = $event->getResource();
        $copy = new ExampleText();
        $copy->setText($resource->getText());
        $copy->setName('copy');
        $event->setCopy($copy);
        $event->stopPropagation();
    }

    //Fired once a resource is exported (downloaded).
    public function onExport(ExportResourceEvent $event)
    {
        $exampleText = $this->container->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\ExampleTextBundle\Entity\ExampleText')
            ->find($event->getResourceId());

        //create new temporary file wich contains our text.
        $tmpfname = tempnam(sys_get_temp_dir(), 'clarotemp').".txt";
        //the name of the exported file will be its current name with the $tmpfname extension.
        file_put_contents($tmpfname, $exampleText->getText());
        $event->setItem($tmpfname);
        $event->stopPropagation();
    }

    //Open is a custom action.
    //Here we're going to do a redirection to the ExampleTextController and keep the workspace context.
    //While we were working with resource in the other event, this one works with instances (links)
    //because they allow us to keep the context (workspace).
    public function onOpen(CustomActionResourceEvent $event)
    {
        //Redirection to the controller.
        $route = $this->container->get('router')->generate('claro_exampletext_open', array('exampleTextInstanceId' => $event->getInstanceId()));
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
