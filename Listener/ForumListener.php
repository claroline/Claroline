<?php

namespace Claroline\ForumBundle\Listener;

use Claroline\CoreBundle\Library\Event\PluginOptionsEvent;
use Claroline\CoreBundle\Library\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Event\CreateResourceEvent;
use Claroline\CoreBundle\Library\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Library\Event\OpenResourceEvent;
use Claroline\CoreBundle\Library\Event\ImportResourceTemplateEvent;
use Claroline\CoreBundle\Library\Event\ExportResourceTemplateEvent;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Form\ForumOptionsType;
use Claroline\ForumBundle\Form\ForumType;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ForumListener extends ContainerAware
{
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new ForumType, new Forum());
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:create_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'claroline_forum'
            )
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->container->get('form.factory')->create(new ForumType, new Forum());
        $form->bindRequest($request);

        if ($form->isValid()) {
            $forum = $form->getData();
            $event->setResource($forum);
            $event->stopPropagation();

            return;
        }

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:create_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'claroline_forum'
            )
        );
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    public function onOpen(OpenResourceEvent $event)
    {
        $route = $this->container
            ->get('router')
            ->generate('claro_forum_open', array('resourceId' => $event->getResource()->getId()));
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    public function onAdministrate(PluginOptionsEvent $event)
    {
        $forumOptions = $this->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineForumBundle:ForumOptions')->findAll();
        $form = $this->container->get('form.factory')->create(new ForumOptionsType, $forumOptions[0]);
        $content = $this->container->get('templating')->render(
            'ClarolineForumBundle::plugin_options_form.html.twig', array(
            'form' => $form->createView()
            )
        );
        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    public function onDelete(DeleteResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($event->getResource());
        $event->stopPropagation();
    }
    
    public function onExportTemplate(ExportResourceTemplateEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $resource = $event->getResource();
        $config['name'] = $resource->getName();
        $config['type'] = 'claroline_forum';
        $subjects = array();
        $datas = $em->getRepository('ClarolineForumBundle:Forum')->getSubjects($resource);
        
        foreach ($datas as $data) {
            $subjects[] = $data['title'];
        }
        
        $config['subjects'] = $subjects;
        $event->setConfig($config);
        $event->stopPropagation();
    }
    
    //@ todo refactoring message name & content.
    public function onImportTemplate(ImportResourceTemplateEvent $event)
    {
        $config = $event->getConfig();
        $forum = new Forum();
        $forum->setName($config['name']);
        $this->container->get('claroline.resource.manager')->create($forum, $event->getParent()->getId(), 'claroline_forum');
        
        foreach ($config['subjects'] as $title) {
            $subject = new Subject();
            $subject->setName($title);
            $subject->setTitle($title);
            $this->container->get('claroline.resource.manager')->create($subject, $forum->getId(), 'claroline_subject');
            $message = new Message();
            $message->setContent('tmp');
            $message->setName('msg-date-tmp');
            $this->container->get('claroline.resource.manager')->create($message, $subject->getId(), 'claroline_message');
        }
        
    }
}