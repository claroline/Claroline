<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;

class HomeListener
{
    /** @var FinderProvider */
    private $finder;

    /**
     * HomeListener constructor.
     *
     * @param FinderProvider $finder
     */
    public function __construct(FinderProvider $finder)
    {
        $this->finder = $finder;
    }

    /**
     * Displays home administration tool.
     *
     * @param OpenToolEvent $event
     */
    public function onDisplayTool(OpenToolEvent $event)
    {
        $homeTabs = $this->finder->search(
          HomeTab::class,
          ['filters' => ['type' => HomeTab::TYPE_ADMIN]]
        );

        $tabs = array_filter($homeTabs['data'], function ($data) {
            return $data !== [];
        });
        $orderedTabs = [];

        foreach ($tabs as $tab) {
            $orderedTabs[$tab['position']] = $tab;
        }
        ksort($orderedTabs);

        $event->setData([
            'editable' => true,
            'administration' => true,
            'tabs' => array_values($orderedTabs),
        ]);
        $event->stopPropagation();
    }
}
