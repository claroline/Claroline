<?php

namespace Claroline\ForumBundle\Listener;

use Claroline\CoreBundle\Event\Event\PluginOptionsEvent;
use Claroline\CoreBundle\Event\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\Event\ImportResourceTemplateEvent;
use Claroline\CoreBundle\Event\Event\ExportResourceTemplateEvent;
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
            'ClarolineCoreBundle:Resource:createForm.html.twig',
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
        $form->handleRequest($request);

        if ($form->isValid()) {
            $forum = $form->getData();
            $event->setResources(array($forum));
            $event->stopPropagation();

            return;
        }

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
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
            ->generate('claro_forum_subjects', array('forumId' => $event->getResource()->getId()));
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
            'ClarolineForumBundle::pluginOptionsForm.html.twig', array(
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

    public function onCopy(CopyResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $resource = $event->getResource();
        $forum = new Forum();
        $forum->setName($resource->getName());
        $oldSubjects = $forum->getSubjects();

        foreach ($oldSubjects as $oldSubject) {
            $newSubject = new Subject;
            $newSubject->setForum($forum);
            $newSubject->setTitle($oldSubject->getTitle());
            $newSubject->setCreator($oldSubject->getCreator());
            $oldMessages = $oldSubjects->getMessages();

            foreach ($oldMessages as $oldMessage) {
                $newMessage = new Message();
                $newMessage->setSubject($newSubject);
                $newMessage->setCreator($oldMessage->getCreator());
                $newMessage->setContent($oldMessage->getContent());

                $em->persist($newMessage);
            }

            $em->persist($newSubject);
        }

        $event->setCopy($forum);
        $event->stopPropagation();
    }

    public function onExportTemplate(ExportResourceTemplateEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $resource = $event->getResource();
        $config['type'] = 'claroline_forum';
        $datas = $em->getRepository('ClarolineForumBundle:Forum')->findSubjects($resource);

        foreach ($datas as $data) {
            $subjects['title'] = $data->getTitle();
            $message = $em->getRepository('ClarolineForumBundle:Message')
                ->findInitialBySubject($data->getId());
            $subjects['initial_message'] = $message->getContent();
            $subjectsData[] = $subjects;
        }

        $config['subjects'] = $subjectsData;
        $event->setConfig($config);
        $event->stopPropagation();
    }

    public function onImportTemplate(ImportResourceTemplateEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $config = $event->getConfig();
        $forum = new Forum();
        $user = $event->getUser();

        foreach ($config['subjects'] as $subject) {
            $subjectEntity = new Subject();
            $subjectEntity->setTitle($subject['title']);
            $subjectEntity->setForum($forum);
            $subjectEntity->setCreator($user);
            $message = new Message();
            $message->setCreator($user);
            $message->setContent($subject['initial_message']);
            $message->setSubject($subjectEntity);
            $em->persist($subjectEntity);
            $em->persist($message);
        }

        $event->setResource($forum);
        $event->stopPropagation();
    }
}