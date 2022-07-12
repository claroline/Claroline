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
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\ExportResourceEvent;
use Claroline\CoreBundle\Event\Resource\ImportResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Manager\ForumManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ForumListener
{
    /** @var ObjectManager */
    private $om;

    /** @var SerializerProvider */
    private $serializer;

    /** @var Crud */
    private $crud;

    /** @var ForumManager */
    private $manager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer,
        Crud $crud,
        ForumManager $manager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->crud = $crud;
        $this->manager = $manager;
        $this->tokenStorage = $tokenStorage;
    }

    public function onOpen(LoadResourceEvent $event)
    {
        /** @var Forum $forum */
        $forum = $event->getResource();
        /** @var User|string $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $isValidatedUser = false;

        if ($user instanceof User) {
            $validationUser = $this->manager->getValidationUser($user, $forum);
            $isValidatedUser = $validationUser->getAccess();
        }

        $event->setData([
            'forum' => $this->serializer->serialize($forum),
            'isValidatedUser' => $isValidatedUser,
        ]);

        $event->stopPropagation();
    }

    public function onCopy(CopyResourceEvent $event)
    {
        /** @var Forum $forum */
        $forum = $event->getResource();
        /** @var Forum $copy */
        $copy = $event->getCopy();

        $this->om->startFlushSuite();
        foreach ($forum->getSubjects() as $subject) {
            $newSubject = new Subject();
            $newSubject->setForum($copy);

            $this->crud->create($newSubject, $this->serializer->serialize($subject), [
                Crud::NO_PERMISSIONS, // this has already been checked by the core before forwarding the copy
                Options::REFRESH_UUID,
            ]);
        }
        $this->om->endFlushSuite();

        $event->stopPropagation();
    }

    public function onExport(ExportResourceEvent $event)
    {
        /** @var Forum $forum */
        $forum = $event->getResource();

        // maybe also export Forum messages
        $event->setData([
            'subjects' => array_map(function (Subject $subject) {
                return $this->serializer->serialize($subject);
            }, $forum->getSubjects()->toArray()),
        ]);
    }

    public function onImport(ImportResourceEvent $event)
    {
        $data = $event->getData();
        /** @var Forum $forum */
        $forum = $event->getResource();

        $this->om->startFlushSuite();
        foreach ($data['subjects'] as $subjectData) {
            unset($subjectData['forum']);

            $subject = new Subject();
            $subject->setForum($forum);

            // TODO : this should use the copy action
            $this->crud->create($subject, $subjectData, [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION, Options::REFRESH_UUID]);
        }
        $this->om->endFlushSuite();
    }
}
