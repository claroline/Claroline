<?php

namespace Claroline\CoreBundle\Library\Testing;

use Claroline\CoreBundle\DataFixtures\LoadPlatformRolesData;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadGroupData;
use Claroline\CoreBundle\Tests\DataFixtures\LoadRoleData;
use Claroline\CoreBundle\Tests\DataFixtures\LoadUserData;
use Claroline\CoreBundle\Tests\DataFixtures\LoadWorkspaceData;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

abstract class FixtureTestCase extends TransactionalTestCase
{
    /** @var EntityManager */
    protected $em;

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

    protected function loadWorkspaceFixture($workspaces = null)
    {
        $this->loadFixture(new LoadWorkspaceData($workspaces));
    }

    protected function loadUserFixture($users = null)
    {
        $this->loadFixture(new LoadPlatformRolesData());
        $this->loadFixture(new LoadUserData($users));
    }

    protected function loadGroupFixture($groups = null)
    {
        $this->loadFixture(new LoadRoleData());
        $this->loadFixture(new LoadGroupData($groups));
    }

    protected function loadRoleFixture()
    {
        $this->loadFixture(new LoadRoleData());
    }

    protected function loadFixture(FixtureInterface $fixture)
    {
        if ($fixture instanceof AbstractFixture) {
            $fixture->setReferenceRepository($this->referenceRepo);
        }

        if ($fixture instanceof ContainerAwareInterface) {
            $fixture->setContainer($this->client->getContainer());
        }

        $fixture->load($this->em);
    }

    protected function getFixtureReference($name)
    {
        return $this->referenceRepo->getReference($name);
    }

    /** @return EntityManager */
    protected function getEntityManager()
    {
        return $this->em;
    }
}