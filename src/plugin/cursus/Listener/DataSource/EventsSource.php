<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\CursusBundle\Entity\Event;
use Claroline\CursusBundle\Repository\SessionRepository;

class EventsSource
{
    /** @var FinderProvider */
    private $finder;

    /** @var SessionRepository */
    private $sessionRepo;

    public function __construct(FinderProvider $finder, ObjectManager $om)
    {
        $this->finder = $finder;
        $this->sessionRepo = $om->getRepository(Session::class);
    }

    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions();

        $options['hiddenFilters']['terminated'] = true;
        $options['hiddenFilters']['session'] = $this->sessionRepo->findByWorkspace($event->getWorkspace());

        $event->setData(
            $this->finder->search(Event::class, $options)
        );

        $event->stopPropagation();
    }
}
