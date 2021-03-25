<?php

namespace Claroline\OpenBadgeBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;

class BadgesSource
{
    /** @var FinderProvider */
    private $finder;

    /**
     * BadgesSource constructor.
     */
    public function __construct(FinderProvider $finder)
    {
        $this->finder = $finder;
    }

    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions() ? $event->getOptions() : [];
        $options['hiddenFilters']['meta.enabled'] = true;

        if (DataSource::CONTEXT_WORKSPACE === $event->getContext()) {
            $options['hiddenFilters']['workspace'] = $event->getWorkspace()->getUuid();
        }

        $event->setData(
            $this->finder->search(BadgeClass::class, $options)
        );

        $event->stopPropagation();
    }
}
