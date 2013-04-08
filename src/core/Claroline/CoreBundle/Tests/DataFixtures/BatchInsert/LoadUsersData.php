<?php

namespace Claroline\CoreBundle\Tests\DataFixtures\BatchInsert;

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
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $userCreator = $this->container->get('claroline.user.creator');
        $role = $manager->getRepository('ClarolineCoreBundle:Role')->findOneBy(array('name' => 'ROLE_USER'));
        $count = $manager->getRepository('ClarolineCoreBundle:User')->count();
        $totalUsers = $count + 1;

        $start = time();

        for ($j = 0, $i = 0; $i < $this->numberUsers; $i++, $totalUsers++) {
            $user = new User();
            $mandatoryFieldValue = "user_{$totalUsers}";
            $user->setFirstName($mandatoryFieldValue);
            $user->setLastName($mandatoryFieldValue);
            $user->setUsername($mandatoryFieldValue);
            $user->setPlainPassword($mandatoryFieldValue);
            $user->addRole($role);
            $userCreator->create($user);

            $this->log("UOW[{$manager->getUnitOfWork()->size()}]");
            if (($i % self::BATCH_SIZE) === 0) {
                $j++;
                $manager->flush();
                $manager->clear();
                $role = $manager->getRepository('ClarolineCoreBundle:Role')->findOneBy(array('name' => 'ROLE_USER'));
                $totalInserts = $i + 1;
                $this->log("batch [{$j}] | users [{$totalInserts}] | UOW  [{$manager->getUnitOfWork()->size()}]");
            }
        }

        $end = time();
        $duration = $this->container->get('claroline.utilities.misc')->timeElapsed($end - $start);
        $this->log("Time elapsed for the user creation: " . $duration);

        return $duration;
    }

}