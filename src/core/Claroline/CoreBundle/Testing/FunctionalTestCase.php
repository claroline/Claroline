<?php

namespace Claroline\CoreBundle\Testing;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Claroline\CoreBundle\Testing\TransactionalTestCase;
use Claroline\CoreBundle\DataFixtures\LoadPlatformRolesData;
use Claroline\CoreBundle\Tests\DataFixtures\LoadUserData;
use Claroline\CoreBundle\Entity\User;

abstract class FunctionalTestCase extends TransactionalTestCase
{
    /** @var Doctrine\ORM\EntityManager */
    private $em;

    /** @var ReferenceRepository */
    private $referenceRepo;
    
    public function setUp()
    {
        parent::setUp();
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->referenceRepo = new ReferenceRepository($this->em);
    }
    
    protected function loadUserFixture()
    {
        $roleFixture = new LoadPlatformRolesData();
        $roleFixture->setReferenceRepository($this->referenceRepo);
        $roleFixture->load($this->em);
        $userFixture = new LoadUserData();
        $userFixture->setContainer($this->client->getContainer());
        $userFixture->setReferenceRepository($this->referenceRepo);
        
        return $userFixture->load($this->em);
    }

    protected function getFixtureReference($name)
    {
        return $this->referenceRepo->getReference($name);
    }
    
    protected function logUser(User $user)
    {
        $this->client->request('GET', '/logout');
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->filter('#login_form input[type=submit]')->form();
        $form['_username'] = $user->getUsername();
        $form['_password'] = $user->getPlainPassword();
        
        return $this->client->submit($form);
    }
    
    /** @return \Symfony\Component\Security\Core\SecurityContextInterface */
    protected function getSecurityContext()
    {
        return $this->client->getContainer()->get('security.context');
    }
}