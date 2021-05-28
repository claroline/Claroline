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
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CursusBundle\Entity\Course;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AllCoursesSource
{
    /** @var FinderProvider */
    private $finder;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        FinderProvider $finder,
        TokenStorageInterface $tokenStorage
    ) {
        $this->finder = $finder;
        $this->tokenStorage = $tokenStorage;
    }

    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions();

        $user = $this->tokenStorage->getToken()->getUser();
        $options['organizations'] = array_map(function(Organization $organization) {
            return $organization->getUuid();
        }, $user->getOrganizations());

        $event->setData(
            $this->finder->search(Course::class, $options)
        );

        $event->stopPropagation();
    }
}
