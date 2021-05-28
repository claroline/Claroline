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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AllCoursesSource
{
    /** @var FinderProvider */
    private $finder;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    private $authorization;

    public function __construct(
        FinderProvider $finder,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->finder = $finder;
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
    }

    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions();

        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();

            $options['hiddenFilters']['organizations'] = array_map(function(Organization $organization) {
                return $organization->getUuid();
            }, $user->getOrganizations());
        }

        $event->setData(
            $this->finder->search(Course::class, $options)
        );

        $event->stopPropagation();
    }
}
