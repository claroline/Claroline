<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\BatchInsert;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Library\Fixtures\LoggableFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;

/**
 * Loads a large amount of users.
 */
class LoadUsersData extends LoggableFixture implements ContainerAwareInterface
{
    private $container;
    private $numberUsers;
    const BATCH_SIZE = 5;

    public function __construct($numberUsers)
    {
        $this->numberUsers = $numberUsers;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $start = time();
        $countUser = $this->container->get('claroline.persistence.object_manager')->count('Claroline\CoreBundle\Entity\User');

        for ($i = 0; $i < $this->numberUsers; ++$i) {
            $totalUsers = $countUser + $i;
            $mandatoryFieldValue = "user_{$totalUsers}";
            $users[] = array(
                $mandatoryFieldValue,
                $mandatoryFieldValue,
                $mandatoryFieldValue,
                $mandatoryFieldValue,
                $mandatoryFieldValue,
            );
        }

        $this->container->get('claroline.manager.user_manager')->importUsers($users, false);
        $end = time();
        $duration = $this->container->get('claroline.utilities.misc')->timeElapsed($end - $start);
        $this->log('Time elapsed for the user creation: '.$duration);

        return $duration;
    }
}
