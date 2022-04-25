<?php

namespace Claroline\TagBundle\Listener\User;

use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\CoreBundle\Entity\User;
use Claroline\TagBundle\Manager\TagManager;

class UserListener
{
    /** @var TagManager */
    private $manager;

    public function __construct(TagManager $manager)
    {
        $this->manager = $manager;
    }

    public function onDelete(DeleteEvent $event)
    {
        /** @var User $user */
        $user = $event->getObject();

        $this->manager->removeTaggedObjectsByClassAndIds(User::class, [$user->getId()]);
    }
}
