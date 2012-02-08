<?php

namespace Claroline\CoreBundle\Testing;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Claroline\CoreBundle\Testing\TransactionalTestCase;
use Claroline\CoreBundle\DataFixtures\LoadPlatformRolesData;
use Claroline\CoreBundle\Tests\DataFixtures\LoadUserData;
use Claroline\CoreBundle\Tests\DataFixtures\LoadRoleData;
use Claroline\CoreBundle\Tests\DataFixtures\LoadGroupData;
use Claroline\CoreBundle\Entity\User;

abstract class FixtureTestCase extends TransactionalTestCase
{
    /** @var Doctrine\ORM\EntityManager */
    private $em;

    /** @var ReferenceRepository */
    private $referenceRepo;
    
    protected function setUp()
    {
        parent::setUp();
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->referenceRepo = new ReferenceRepository($this->em);
    }
    
    protected function loadPlatformRolesFixture()
    {
        $this->loadFixture(new LoadPlatformRolesData());
    }
    
    protected function loadUserFixture()
    {
        $this->loadFixture(new LoadPlatformRolesData());
        $this->loadFixture(new LoadUserData());
    }

    protected function loadGroupFixture()
    {
        $this->loadFixture(new LoadRoleData());
        $this->loadFixture(new LoadGroupData());
    }
    
    protected function loadRoleFixture()
    {
        $this->loadFixture(new LoadRoleData());
    }
    
    protected function loadFixture(FixtureInterface $fixture)
    {
        if ($fixture instanceof AbstractFixture)
        {
            $fixture->setReferenceRepository($this->referenceRepo);
        }
        
        if ($fixture instanceof ContainerAwareInterface)
        {
            $fixture->setContainer($this->client->getContainer());
        }
        
        $fixture->load($this->em);
    }
    
    protected function getFixtureReference($name)
    {
        return $this->referenceRepo->getReference($name);
    }
    
    /** @return Doctrine\ORM\EntityManager */
    protected function getEntityManager()
    {
        return $this->em;
    }
}