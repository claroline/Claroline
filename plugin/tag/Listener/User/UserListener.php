<?php

namespace Claroline\TagBundle\Listener\User;

use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User as UserEntity;
use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Claroline\TagBundle\Entity\Tag;
use Claroline\TagBundle\Manager\TagManager;
use Claroline\TagBundle\Repository\TagRepository;

class UserListener
{
    /** @var ObjectManager */
    private $om;

    /** @var TagManager */
    private $manager;

    /** @var TagRepository */
    private $repository;

    /**
     * UserListener constructor.
     *
     * @param ObjectManager $om
     * @param TagManager    $manager
     */
    public function __construct(
        ObjectManager $om,
        TagManager $manager
    ) {
        $this->om = $om;
        $this->manager = $manager;

        $this->repository = $om->getRepository(Tag::class);
    }

    /**
     * @param DeleteEvent $event
     */
    public function onDelete(DeleteEvent $event)
    {
        /** @var UserEntity $user */
        $user = $event->getObject();

        $this->manager->removeTaggedObjectsByClassAndIds(UserEntity::class, [$user->getId()]);
    }

    /**
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        $tags = $this->repository->findBy([
            'user' => $event->getRemoved(),
        ]);

        if (count($tags) > 0) {
            foreach ($tags as $tag) {
                $tag->setUser($event->getKept());
            }

            $this->om->flush();

            $event->addMessage('[ClarolineTagBundle] updated Tag count: '.count($tags));
        }
    }
}
