<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Listener\DataSource;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\Role;

class AnnouncementSource
{
    /** @var FinderProvider */
    private $finder;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * AnnouncementSource constructor.
     *
     * @param FinderProvider        $finder
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
      FinderProvider $finder,
      TokenStorageInterface $tokenStorage
    ) {
        $this->finder = $finder;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param GetDataEvent $event
     */
    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions() ? $event->getOptions() : [];
        $options['hiddenFilters']['visible'] = true;
        $options['hiddenFilters']['published'] = true;

        if (DataSource::CONTEXT_HOME === $event->getContext()) {
            // only what is accessible by anonymous
            $options['hiddenFilters']['roles'] = ['ROLE_ANONYMOUS'];
        } else {
            // filter by current user roles
            $options['hiddenFilters']['roles'] = array_map(
                function (Role $role) { return $role->getRole(); },
                $this->tokenStorage->getToken()->getRoles()
            );
        }

        if (DataSource::CONTEXT_WORKSPACE === $event->getContext()) {
            $options['hiddenFilters']['workspace'] = $event->getWorkspace()->getUuid();
        } else {
            $options['hiddenFilters']['archived'] = false;
        }

        $event->setData(
            $this->finder->search(Announcement::class, $options)
        );

        $event->stopPropagation();
    }
}
