<?php
namespace Claroline\UserBundle\Tests\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Claroline\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUserData implements FixtureInterface, ContainerAwareInterface
{
    /** @var ContainerInterface $container */
    private $container;
    
    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }
    
    public function load($manager) {
        $user = new User();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setUserName('jdoe');
        $user->setPlainPassword('topsecret');
        
        $factory = $this->container->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);
        $password = $encoder->encodePassword($user->getPlainPassword(), $user->getSalt());
        
        $user->setPassword($password);

        
        
        $manager->persist($user);
        $manager->flush();
    }
    
}
