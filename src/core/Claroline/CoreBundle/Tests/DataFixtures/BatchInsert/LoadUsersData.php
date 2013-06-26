<?php

namespace Claroline\CoreBundle\Tests\DataFixtures\BatchInsert;

use Claroline\CoreBundle\Library\Fixtures\LoggableFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;

/**
 * Loads a large amount of users.
 */
class LoadUsersData extends LoggableFixture
{
    private $numberUsers;
    const BATCH_SIZE = 5;

    public function __construct($numberUsers)
    {
        $this->numberUsers = $numberUsers;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $userCreator = $this->container->get('claroline.user.creator');
        $start = time();
        $countUser = $manager->getRepository('ClarolineCoreBundle:User')->count();

        for ($i = 0; $i < $this->numberUsers; $i++) {
            $totalUsers = $countUser + $i;
            $mandatoryFieldValue = "user_{$totalUsers}";
            $users[] = array(
                $mandatoryFieldValue,
                $mandatoryFieldValue,
                $mandatoryFieldValue,
                $mandatoryFieldValue,
                $mandatoryFieldValue
            );
        }

        $userCreator->import($users);
        $end = time();
        $duration = $this->container->get('claroline.utilities.misc')->timeElapsed($end - $start);
        $this->log("Time elapsed for the user creation: " . $duration);

        return $duration;
    }

}