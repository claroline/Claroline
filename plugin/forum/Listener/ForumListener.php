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
use Claroline\CoreBundle\Event\ResourceCreatedEvent;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Form\ForumType;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ForumListener extends ContainerAware
{
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new ForumType(), new Forum());
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'claroline_forum',
            )
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->container->get('form.factory')->create(new ForumType(), new Forum());
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
                'resourceType' => 'claroline_forum',
            )
        );
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    public function onOpen(OpenResourceEvent $event)
    {
        $requestStack = $this->container->get('request_stack');
        $httpKernel = $this->container->get('http_kernel');
        $request = $requestStack->getCurrentRequest();
        $params = array();
        $params['_controller'] = 'ClarolineForumBundle:Forum:open';
        $params['forum'] = $event->getResource()->getId();
        $subRequest = $request->duplicate(array(), null, $params);
        $response = $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
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
        $event->setCopy($this->container->get('claroline.manager.forum_manager')->copy($resource));
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

    public function onResourceCreated(ResourceCreatedEvent $event)
    {
        $node = $event->getResourceNode();
        $this->container->get('claroline.manager.forum_manager')->createDefaultPostRights($node);
    }
}
