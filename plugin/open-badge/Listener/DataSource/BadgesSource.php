<?php

namespace Claroline\OpenBadgeBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class BadgesSource
{
    /** @var FinderProvider */
    private $finder;

    /**
     * BadgesSource constructor.
     *
     * @DI\InjectParams({
     *     "finder" = @DI\Inject("claroline.api.finder")
     * })
     *
     * @param FinderProvider $finder
     */
    public function __construct(FinderProvider $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @DI\Observe("data_source.badges.load")
     *
     * @param GetDataEvent $event
     */
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
