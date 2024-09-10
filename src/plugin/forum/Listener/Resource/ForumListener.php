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
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Entity\Validation\User as ForumUser;
use Claroline\ForumBundle\Manager\ForumManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ForumListener extends ResourceComponent
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud,
        private readonly FinderProvider $finder,
        private readonly ForumManager $manager
    ) {
    }

    public static function getName(): string
    {
        return 'claroline_forum';
    }

    /** @var Forum $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        /** @var User|string $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $validationUser = null;
        if ($user instanceof User) {
            $validationUser = $this->manager->getValidationUser($user, $resource);
        }

        $myMessages = 0;
        if ($this->tokenStorage->getToken()->getUser() instanceof User) {
            $myMessages = $this->finder->fetch(Message::class, [
                'forum' => $resource->getUuid(),
                'creator' => $this->tokenStorage->getToken()->getUser()->getUuid(),
            ], null, 0, 0, true);
        }

        return [
            'forum' => $this->serializer->serialize($resource),
            'tags' => $this->getTags($resource),
            'users' => $this->finder->fetch(ForumUser::class, ['forum' => $resource->getUuid(), 'banned' => false], null, 0, 0, true),
            'subjects' => $this->finder->fetch(Subject::class, ['forum' => $resource->getUuid(), 'flagged' => false], null, 0, 0, true),
            'messages' => $this->finder->fetch(Message::class, ['forum' => $resource->getUuid(), 'flagged' => false], null, 0, 0, true),

            'isValidatedUser' => $validationUser && $validationUser->getAccess(),
            'banned' => $validationUser && $validationUser->isBanned(),
            'notified' => $validationUser && $validationUser->isNotified(),
            'myMessages' => $myMessages,
        ];
    }

    public function update(AbstractResource $resource, array $data): ?array
    {
        return [
            'resource' => $this->serializer->serialize($resource),
        ];
    }

    /**
     * @param Forum $original
     * @param Forum $copy
     */
    public function copy(AbstractResource $original, AbstractResource $copy): void
    {
        $this->om->startFlushSuite();

        $subjects = $original->getSubjects()->toArray();
        foreach ($subjects as $subject) {
            $subjectData = $this->serializer->serialize($subject);
            unset($subjectData['forum']);

            $newSubject = new Subject();
            $newSubject->setForum($copy);

            $this->crud->create($newSubject, $subjectData, [
                Crud::NO_PERMISSIONS, // this has already been checked by the core before forwarding the copy
                Crud::NO_VALIDATION, // we pass data directly from the serializer, we don't need to valid it
                Options::REFRESH_UUID,
            ]);
        }

        $this->om->endFlushSuite();
    }

    /** @var Forum $resource */
    public function export(AbstractResource $resource, FileBag $fileBag): ?array
    {
        $subjects = $resource->getSubjects()->toArray();

        return [
            'subjects' => array_map(function (Subject $subject) {
                return $this->serializer->serialize($subject);
            }, $subjects),
        ];
    }

    /** @var Forum $resource */
    public function import(AbstractResource $resource, FileBag $fileBag, array $data = []): void
    {
        if (empty($data['subjects'])) {
            return;
        }

        $this->om->startFlushSuite();

        foreach ($data['subjects'] as $subjectData) {
            unset($subjectData['forum']);

            $subject = new Subject();
            $subject->setForum($resource);

            $this->crud->create($subject, $subjectData, [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION, Options::REFRESH_UUID]);
        }

        $this->om->endFlushSuite();
    }

    private function getTags(Forum $forum): array
    {
        $subjects = $forum->getSubjects();
        $available = [];

        foreach ($subjects as $subject) {
            $event = new GenericDataEvent([
                'class' => Subject::class,
                'ids' => [$subject->getUuid()],
            ]);

            $this->eventDispatcher->dispatch(
                $event,
                'claroline_retrieve_used_tags_object_by_class_and_ids'
            );

            $tags = $event->getResponse() ?? [];
            $available = array_merge($available, $tags);
        }

        return $available;
    }
}
