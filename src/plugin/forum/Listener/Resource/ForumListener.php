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
use Claroline\CoreBundle\Event\ImportObjectEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
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
}
