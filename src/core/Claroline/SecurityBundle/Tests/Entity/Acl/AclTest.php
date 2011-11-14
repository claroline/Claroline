<?php

namespace Claroline\SecurityBundle\Entity\Acl;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AclTest extends WebTestCase
{
    private $client;
    private $em;
    
    public function setUp()
    {
        $this->client = self::createClient();
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->client->beginTransaction();
    }
    
    public function tearDown()
    {
        $this->client->rollback();
        parent :: tearDown();
    }
    
    public function testAclEntitiesAreLoadableAndPersistable()
    {
        $aclClass = new AclClass();
        $aclEntry = new AclEntry();
        $aclObjectIdentity = new AclObjectIdentity();
        $aclSecurityIdentity = new AclSecurityIdentity();
        
        $this->em->persist($aclClass);
        $this->em->persist($aclEntry);
        $this->em->persist($aclObjectIdentity);
        $this->em->persist($aclSecurityIdentity);
    }
}