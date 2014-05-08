<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Listener;

use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\DeleteUserEvent;
use Claroline\CoreBundle\Event\ImportResourceTemplateEvent;
use Claroline\CoreBundle\Event\ExportResourceTemplateEvent;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Form\ForumType;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
            $this->container->get('claroline.manager.forum_manager')->createCategory($forum, $forum->getName(), false);
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
            ->generate('claro_forum_categories', array('forum' => $event->getResource()->getId()));
        $event->setResponse(new RedirectResponse($route));
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
        $event->setCopy($this->container->get('claroline.manager.forum_manager')->copy($resource));
        $event->stopPropagation();
    }

    public function onExportTemplate(ExportResourceTemplateEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $resource = $event->getResource();
        $config['type'] = 'claroline_forum';
        $datas = $em->getRepository('ClarolineForumBundle:Forum')->findSubjects($resource);

        foreach ($datas as $data) {
            $subjects['title'] = $data['title'];
            $message = $em->getRepository('ClarolineForumBundle:Message')
                ->findInitialBySubject($data['id']);
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

    public function onDeleteUser(DeleteUserEvent $event)
    {
        //remove notification for user if it exists
        $em = $this->container->get('doctrine.orm.entity_manager');
        $notificationRepo = $em->getRepository('ClarolineForumBundle:Notification');

        $notifications = $notificationRepo->findOneBy(array('user' => $event->getUser()));
        if (count($notifications) > 0) {
            foreach ($notifications as $notification) {
            $em->remove($notification);
        }
            $em->flush();
        }            
    }


}
