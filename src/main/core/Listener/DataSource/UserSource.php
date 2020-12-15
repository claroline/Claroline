<?php

namespace Claroline\CoreBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;

class UserSource
{
    /** @var FinderProvider */
    private $finder;

    /**
     * UserSource constructor.
     *
     * @param FinderProvider $finder
     */
    public function __construct(
        FinderProvider $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @param GetDataEvent $event
     */
    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions();
        if (DataSource::CONTEXT_WORKSPACE === $event->getContext()) {
            $options['hiddenFilters']['workspace'] = $event->getWorkspace()->getUuid();
        }

        $event->setData(
            $this->finder->search(User::class, $options)
        );

        $event->stopPropagation();
    }
}
