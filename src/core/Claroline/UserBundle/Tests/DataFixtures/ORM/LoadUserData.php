<?php
namespace Claroline\UserBundle\Tests\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Claroline\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;

class LoadUserData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface
{
    /** @var ContainerInterface $container */
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load($manager)
    {
        $admin = new User();
        $admin->setFirstName('Barack');
        $admin->setLastName('Obama');
        $admin->setUserName('admin');
        $admin->setPlainPassword('USA');
        
        $jdoe = new User();
        $jdoe->setFirstName('John');
        $jdoe->setLastName('Doe');
        $jdoe->setUserName('jdoe');
        $jdoe->setPlainPassword('topsecret');

        $userManager = $this->container->get('claroline.user.manager');
        $userManager->create($admin);
        $userManager->create($jdoe);

        $this->addReference('user/admin', $admin);
        $this->addReference('user/jdoe', $jdoe);
    }
}