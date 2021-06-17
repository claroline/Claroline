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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\ExportObjectEvent;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\ImportObjectEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Claroline\EvaluationBundle\Entity\Evaluation\AbstractEvaluation;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Manager\ForumManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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

    /** @var ForumManager */
    private $manager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * ForumListener constructor.
     */
    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer,
        Crud $crud,
        ResourceEvaluationManager $evaluationManager,
        ForumManager $manager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->crud = $crud;
        $this->evaluationManager = $evaluationManager;
        $this->manager = $manager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Loads a Forum resource.
     */
    public function onOpen(LoadResourceEvent $event)
    {
        /** @var Forum $forum */
        $forum = $event->getResource();
        /** @var User|string $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $isValidatedUser = false;

        if ('anon.' !== $user) {
            $validationUser = $this->manager->getValidationUser($user, $forum);
            $isValidatedUser = $validationUser->getAccess();
        }

        $event->setData([
            'forum' => $this->serializer->serialize($forum),
            'isValidatedUser' => $isValidatedUser,
        ]);

        $event->stopPropagation();
    }

    public function onExport(ExportObjectEvent $exportEvent)
    {
        /** @var Forum $forum */
        $forum = $exportEvent->getObject();
        $data = [
          'subjects' => array_map(function (Subject $subject) {
              return $this->serializer->serialize($subject);
          }, $forum->getSubjects()->toArray()),
        ];
        $exportEvent->overwrite('_data', $data);
    }

    public function onImportBefore(ImportObjectEvent $event)
    {
        $data = $event->getData();
        $replaced = json_encode($event->getExtra());

        foreach ($data['_data']['subjects'] as $subjectsData) {
            $uuid = Uuid::uuid4()->toString();
            $replaced = str_replace($subjectsData['id'], $uuid, $replaced);
        }

        $data = json_decode($replaced, true);
        $event->setExtra($data);
    }

    public function onImport(ImportObjectEvent $event)
    {
        $data = $event->getData();
        $forum = $event->getObject();

        foreach ($data['_data']['subjects'] as $subjectsData) {
            unset($subjectsData['forum']);
            $subject = $this->serializer->deserialize($subjectsData, new Subject());
            $subject->setForum($forum);
            $this->om->persist($subject);
        }
    }

    /**
     * Creates evaluation for forum resource.
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
            $status = AbstractEvaluation::STATUS_UNKNOWN;
            $nbAttempts = 0;
            $nbOpenings = 0;

            foreach ($logs as $log) {
                switch ($log->getAction()) {
                    case 'resource-read':
                        ++$nbOpenings;

                        if (AbstractEvaluation::STATUS_UNKNOWN === $status) {
                            $status = AbstractEvaluation::STATUS_OPENED;
                        }
                        break;
                    case 'resource-claroline_forum-create_message':
                        ++$nbAttempts;
                        $status = AbstractEvaluation::STATUS_PARTICIPATED;
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
