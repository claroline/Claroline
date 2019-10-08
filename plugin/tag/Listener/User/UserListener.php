<?php

namespace Claroline\TagBundle\Listener\User;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User as UserEntity;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Claroline\TagBundle\Entity\Tag;
use Claroline\TagBundle\Manager\TagManager;
use Claroline\TagBundle\Repository\TagRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
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
     * @DI\InjectParams({
     *     "om"      = @DI\Inject("Claroline\AppBundle\Persistence\ObjectManager"),
     *     "manager" = @DI\Inject("claroline.manager.tag_manager")
     * })
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
     * @DI\Observe("claroline_users_delete")
     *
     * @param GenericDataEvent $event
     */
    public function onDelete(GenericDataEvent $event)
    {
        /** @var UserEntity[] $users */
        $users = $event->getData();

        $ids = [];
        foreach ($users as $user) {
            $ids[] = $user->getId();
        }

        $this->manager->removeTaggedObjectsByClassAndIds(UserEntity::class, $ids);
    }

    /**
     * @DI\Observe("merge_users")
     *
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
