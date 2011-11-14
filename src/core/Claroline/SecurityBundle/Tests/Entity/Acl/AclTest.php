<?php

namespace Claroline\SecurityBundle\Entity\Acl;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\Lib\Testing\TransactionalTestCase;

class AclTest extends TransactionalTestCase
{
    private $em;
    
    public function setUp()
    {
        parent :: setUp();
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
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