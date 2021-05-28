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
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\CursusBundle\Entity\Course;

class AllCoursesSource
{
    /** @var FinderProvider */
    private $finder;

    public function __construct(FinderProvider $finder)
    {
        $this->finder = $finder;
    }

    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions();

        $event->setData(
            $this->finder->search(Course::class, $options)
        );

        $event->stopPropagation();
    }
}
