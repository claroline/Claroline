<?php

namespace ICAP\LessonBundle\listener;

use Claroline\CoreBundle\Library\Event\PluginOptionsEvent;
use Claroline\CoreBundle\Library\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Event\CreateResourceEvent;
use Claroline\CoreBundle\Library\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Library\Event\OpenResourceEvent;
use Claroline\CoreBundle\Library\Event\CopyResourceEvent;
use Claroline\CoreBundle\Library\Event\LogCreateDelegateViewEvent;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

use ICAP\LessonBundle\Form\LessonType;
use ICAP\LessonBundle\Entity\Lesson;
use ICAP\LessonBundle\Controller\LessonController;

class LessonListener extends ContainerAware
{
    /*Méthode permettant de créer le formulaire de creation*/
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new LessonType(), new Lesson());
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:create_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'icap_lesson'
            )
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /*Méthode permettant de créer la table en base*/
    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->container->get('form.factory')->create(new LessonType(), new Lesson());
        $form->handleRequest($request);
        if ($form->isValid()) {
            $lesson = $form->getData();
            $event->setResource($lesson);
        } else {
            $content = $this->container->get('templating')->render(
                'ClarolineCoreBundle:Resource:create_form.html.twig',
                array(
                    'form' => $form->createView(),
                    'resourceType' => 'icap_lesson'
                )
            );
            $event->setErrorFormContent($content);
        }
        $event->stopPropagation();
    }

    public function onOpen(OpenResourceEvent $event)
    {
        $route = $this->container
            ->get('router')
            ->generate(
                'icap_lesson',
                array('resourceId' => $event->getResource()->getId())
                );
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    /*public function onDelete(DeleteResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($event->getResource());
        $event->stopPropagation();
    }*/
}