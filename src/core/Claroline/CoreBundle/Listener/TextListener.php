<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\CoreBundle\Library\Resource\ResourceEvent;
use Claroline\CoreBundle\Form\TextType;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Entity\Resource\Revision;
use Claroline\CoreBundle\Library\Resource\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Resource\CreateResourceEvent;
use Claroline\CoreBundle\Library\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Library\Resource\CustomActionResourceEvent;

class TextListener extends ContainerAware
{
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new TextType, new Text());
        $response = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:resource_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'text'
            )
        );
        $event->setResponseContent($response);
        $event->stopPropagation();
    }

    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $em = $this->container->get('doctrine.orm.entity_manager');
        $user = $this->container->get('security.context')->getToken()->getUser();
        $form = $this->container
            ->get('form.factory')
            ->create(new TextType(), new Text());
        $form->bindRequest($request);

        if ($form->isValid()) {
            $revision = new Revision();
            $revision->setContent($form->getData()->getText());
            $revision->setUser($user);
            $em->persist($revision);
            $text = new Text();
            $text->setLastRevision($revision);
            $text->setName($form->getData()->getName());
            $em->persist($text);
            $revision->setText($text);
            $event->setResource($text);
            $event->stopPropagation();
            return;
        }

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:text:resource_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'text'
            )
        );
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }
    
    public function onOpen(CustomActionResourceEvent $event)
    {
        $text = $this->container->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Resource\Text')->find($event->getResourceId());
        $content = $this->container->get('templating')->render('ClarolineCoreBundle:Text:index.html.twig', array('text' => $text->getLastRevision()->getContent(), 'textId' => $event->getResourceId()));
        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}