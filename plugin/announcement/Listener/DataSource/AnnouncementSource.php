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

        if (DataSource::CONTEXT_WORKSPACE === $event->getContext()) {
            $options['hiddenFilters']['workspace'] = $event->getWorkspace()->getUuid();
        } elseif (DataSource::CONTEXT_HOME === $event->getContext()) {
            $options['hiddenFilters']['anonymous'] = true;
        } else {
            $options['hiddenFilters']['user'] = $this->tokenStorage->getToken()->getUser()->getUuid();
        }

        $event->setData(
            $this->finder->search(Announcement::class, $options)
        );

        $event->stopPropagation();
    }
}
