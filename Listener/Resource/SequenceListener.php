<?php

namespace UJM\ExoBundle\Listener\Resource;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\CustomActionResourceEvent;

use UJM\ExoBundle\Entity\Sequence\Sequence;

class SequenceListener extends ContainerAware {

    /**
     * Fired when a new ResourceNode of type Sequence is edited
     * @param  \Claroline\CoreBundle\Event\CustomActionResourceEvent $event
     * @throws \Exception
     */
    public function onAdministrate(CustomActionResourceEvent $event) {
        $resource = $event->getResource();
        $route = $this->container
                ->get('router')
                ->generate('ujm_sequence_administrate', array('id' => $resource->getId())
        );
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    /**
     * Fired when a new ResourceNode of type Sequence is opened
     * @param  \Claroline\CoreBundle\Event\OpenResourceEvent $event
     * @throws \Exception
     */
    public function onOpen(OpenResourceEvent $event) {
        $resource = $event->getResource();
        //Redirection to the controller.
        $route = $this->container
                ->get('router')
                ->generate('ujm_sequence_open', array('id' => $resource->getId()));
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    /**
     * 
     * @param CreateResourceEvent $event
     * @throws \Exception
     */
    public function onCreate(CreateResourceEvent $event) {
        // Create form
        $form = $this->container->get('form.factory')->create('sequence_type', new Sequence());
        // Try to process form
        $request = $this->container->get('request');
        $form->submit($request);
        if ($form->isValid()) {
            $resource = $form->getData();
            
            $sequence = $this->container->get('ujm_exo_bundle.manager.sequence')->createFirstAndLastStep($resource);
            
            $event->setResources(array($sequence));
            
            
        } else {
            $content = $this->container->get('templating')->render(
                    'ClarolineCoreBundle:Resource:createForm.html.twig', array(
                'form' => $form->createView(),
                'resourceType' => 'ujm_sequence'
            ));
            $event->setErrorFormContent($content);
        }
        $event->stopPropagation();
        return;
    }

    /**
     * 
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event) {
        // Create form
        $form = $this->container->get('form.factory')->create('sequence_type', new Sequence());

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig', array(
            'form' => $form->createView(),
            'resourceType' => 'ujm_sequence'
        ));        
        
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * Fired when a ResourceNode of type Sequence is deleted
     * @param \Claroline\CoreBundle\Event\DeleteResourceEvent $event
     * @throws \Exception
     */
    public function onDelete(DeleteResourceEvent $event) {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $resource = $event->getResource();
        $em->remove($resource);
        $event->stopPropagation();
    }

    /**
     * Fired when a ResourceNode of type Sequence is duplicated
     * @param \Claroline\CoreBundle\Event\CopyResourceEvent $event
     * @throws \Exception
     */
    public function onCopy(CopyResourceEvent $event) {
        $toCopy = $event->getResource();
        $new = new Sequence();
        $new->setName($toCopy->getName());
        $event->setCopy($new);
        $event->stopPropagation();
    }

}
