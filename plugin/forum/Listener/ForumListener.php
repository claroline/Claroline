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

use Claroline\CoreBundle\Entity\Resource\AbstractResourceEvaluation;
use Claroline\CoreBundle\Event\DeleteUserEvent;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\OpenResourceEvent;
use Claroline\ForumBundle\Entity\Forum;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Response;

class ForumListener
{
    use ContainerAwareTrait;

    /**
     * Opens the Forum resource.
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $content = $this->container->get('templating')->render(
            'ClarolineForumBundle:Forum:open.html.twig', [
                '_resource' => $event->getResource(),
                'forum' => $this->container->get('claroline.api.serializer')->serialize($event->getResource()),
            ]
        );

        $event->setResponse(new Response($content));
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
        $resource = $event->getResource();
        $new = $this->container->get('claroline.api.crud')->copy($resource);
        $event->setCopy($new);
        $event->stopPropagation();
    }

    public function onDeleteUser(DeleteUserEvent $event)
    {
        //remove notification for user if it exists
        $em = $this->container->get('doctrine.orm.entity_manager');
        $notificationRepo = $em->getRepository('ClarolineForumBundle:Notification');

        $notifications = $notificationRepo->findOneBy(['user' => $event->getUser()]);
        if (count($notifications) > 0) {
            foreach ($notifications as $notification) {
                $em->remove($notification);
            }
            $em->flush();
        }
    }

    public function onGenerateResourceTracking(GenericDataEvent $event)
    {
        $om = $this->container->get('claroline.persistence.object_manager');
        $resourceEvalManager = $this->container->get('claroline.manager.resource_evaluation_manager');
        $data = $event->getData();
        $node = $data['resourceNode'];
        $user = $data['user'];
        $startDate = $data['startDate'];

        $logs = $resourceEvalManager->getLogsForResourceTracking(
            $node,
            $user,
            ['resource-read', 'resource-claroline_forum-create_message'],
            $startDate
        );

        if (count($logs) > 0) {
            $om->startFlushSuite();
            $tracking = $resourceEvalManager->getResourceUserEvaluation($node, $user);
            $tracking->setDate($logs[0]->getDateLog());
            $status = AbstractResourceEvaluation::STATUS_UNKNOWN;
            $nbAttempts = 0;
            $nbOpenings = 0;

            foreach ($logs as $log) {
                switch ($log->getAction()) {
                    case 'resource-read':
                        ++$nbOpenings;

                        if (AbstractResourceEvaluation::STATUS_UNKNOWN === $status) {
                            $status = AbstractResourceEvaluation::STATUS_OPENED;
                        }
                        break;
                    case 'resource-claroline_forum-create_message':
                        ++$nbAttempts;
                        $status = AbstractResourceEvaluation::STATUS_PARTICIPATED;
                        break;
                }
            }
            $tracking->setStatus($status);
            $tracking->setNbAttempts($nbAttempts);
            $tracking->setNbOpenings($nbOpenings);
            $om->persist($tracking);
            $om->endFlushSuite();
        }
        $event->stopPropagation();
    }
}
