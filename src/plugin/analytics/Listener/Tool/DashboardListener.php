<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnalyticsBundle\Listener\Tool;

use Claroline\AnalyticsBundle\Manager\AnalyticsManager;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;

class DashboardListener
{
    /** @var AnalyticsManager */
    private $manager;

    public function __construct(
        AnalyticsManager $manager
    ) {
        $this->manager = $manager;
    }

    /**
     * Displays dashboard on Workspace.
     */
    public function onDisplayWorkspace(OpenToolEvent $event)
    {
        $workspace = $event->getWorkspace();
        $event->setData([
            'count' => $this->manager->count($workspace),
        ]);
        $event->stopPropagation();
    }

    /**
     * Displays dashboard on Administration.
     */
    public function onDisplayAdministration(OpenToolEvent $event)
    {
        $event->setData([
            'count' => $this->manager->count(),
        ]);
        $event->stopPropagation();
    }
}
