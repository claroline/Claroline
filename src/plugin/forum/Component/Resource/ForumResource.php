<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Component\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Manager\ForumManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ForumResource extends ResourceComponent
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud,
        private readonly ForumManager $manager
    ) {
    }

    public static function getName(): string
    {
        return 'claroline_forum';
    }

    /** @param Forum $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        /** @var User|string $user */
        $user = $this->tokenStorage->getToken()?->getUser();

        $validationUser = null;
        if ($user instanceof User) {
            $validationUser = $this->manager->getValidationUser($user, $resource);
        }

        return [
            'notified' => $validationUser?->isNotified() ?? false,
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

    /** @param Forum $resource */
    public function export(AbstractResource $resource, FileBag $fileBag): ?array
    {
        $subjects = $resource->getSubjects()->toArray();

        return [
            'subjects' => array_map(function (Subject $subject) {
                return $this->serializer->serialize($subject);
            }, $subjects),
        ];
    }

    /** @param Forum $resource */
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
}
