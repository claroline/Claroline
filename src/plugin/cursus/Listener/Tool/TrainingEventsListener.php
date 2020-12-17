<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Listener\Tool;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\CursusBundle\Entity\Session;

class TrainingEventsListener
{
    /** @var FinderProvider */
    private $finder;

    public function __construct(FinderProvider $finder)
    {
        $this->finder = $finder;
    }

    public function onDisplayWorkspace(OpenToolEvent $event)
    {
        $sessionList = $this->finder->search(Session::class, [
            'filters' => ['workspace' => $event->getWorkspace()->getUuid()],
        ], [Options::SERIALIZE_MINIMAL]);

        $event->setData([
            'sessions' => $sessionList['data'],
        ]);
        $event->stopPropagation();
    }
}
