<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Listener\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResourceEvaluation;
use Claroline\CoreBundle\Event\DeleteUserEvent;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class ForumListener
{
    /** @var ObjectManager */
    private $om;

    /** @var SerializerProvider */
    private $serializer;

    /** @var Crud */
    private $crud;

    /** @var ResourceEvaluationManager */
    private $evaluationManager;

    /**
     * ForumListener constructor.
     *
     * @DI\InjectParams({
     *     "om"                = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer"        = @DI\Inject("claroline.api.serializer"),
     *     "crud"              = @DI\Inject("claroline.api.crud"),
     *     "evaluationManager" = @DI\Inject("claroline.manager.resource_evaluation_manager")
     * })
     *
     * @param ObjectManager             $om
     * @param SerializerProvider        $serializer
     * @param Crud                      $crud
     * @param ResourceEvaluationManager $evaluationManager
     */
    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer,
        Crud $crud,
        ResourceEvaluationManager $evaluationManager
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->crud = $crud;
        $this->evaluationManager = $evaluationManager;
    }

    /**
     * Loads a Forum resource.
     *
     * @DI\Observe("resource.claroline_forum.load")
     *
     * @param LoadResourceEvent $event
     */
    public function onOpen(LoadResourceEvent $event)
    {
        $event->setData([
            'forum' => $this->serializer->serialize($event->getResource()),
        ]);

        $event->stopPropagation();
    }

    /**
     * Deletes a forum resource.
     *
     * @DI\Observe("delete_claroline_forum")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $event->stopPropagation();
    }

    /**
     * Copies a forum resource.
     *
     * @DI\Observe("copy_claroline_forum")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $resource = $event->getResource();
        $new = $this->crud->copy($resource);

        $event->setCopy($new);
        $event->stopPropagation();
    }

    /**
     * Removes forum notifications when a user is deleted.
     *
     * @DI\Observe("delete_user")
     *
     * @param DeleteUserEvent $event
     */
    public function onDeleteUser(DeleteUserEvent $event)
    {
        //remove notification for user if it exists
        $notificationRepo = $this->om->getRepository('ClarolineForumBundle:Notification');

        $notifications = $notificationRepo->findBy(['user' => $event->getUser()]);
        if (count($notifications) > 0) {
            foreach ($notifications as $notification) {
                $this->om->remove($notification);
            }
            $this->om->flush();
        }
    }

    /**
     * Creates evaluation for forum resource.
     *
     * @DI\Observe("generate_resource_user_evaluation_claroline_forum")
     *
     * @param GenericDataEvent $event
     */
    public function onGenerateResourceTracking(GenericDataEvent $event)
    {
        $data = $event->getData();
        $node = $data['resourceNode'];
        $user = $data['user'];
        $startDate = $data['startDate'];

        $logs = $this->evaluationManager->getLogsForResourceTracking(
            $node,
            $user,
            ['resource-read', 'resource-claroline_forum-create_message'],
            $startDate
        );

        if (count($logs) > 0) {
            $this->om->startFlushSuite();
            $tracking = $this->evaluationManager->getResourceUserEvaluation($node, $user);
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
            $this->om->persist($tracking);
            $this->om->endFlushSuite();
        }
        $event->stopPropagation();
    }
}
