<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Entity\Resource;

class ResourceManagerTest extends FunctionalTestCase
{
    /** @var Claroline\CoreBundle\Manager\ResourceManager */
    private $resourceManager;
    
    /** @var array[User] */
    private $users;
    
    public function setUp()
    {
        parent::setUp();
        $this->resourceManager = $this->client->getContainer()->get('claroline.resource.manager');
        $this->users = $this->loadUserFixture();
    }
    
    public function testCreateResourceGivesPassedInUserOwnerPermissions()
    {
        $resource = new Resource();
        $this->resourceManager->createResource($resource, $this->users['user']);
        
        $this->logUser($this->users['user']);   
        
        $this->assertTrue($this->getSecurityContext()->isGranted('OWNER', $resource));
    }
    
//    public function test() // cf RightManager::getAllowedEntityIdsByUser
//    {
//        $user = new User();
//        $user->setFirstName('John');
//        $user->setLastName('Doe');
//        $user->setUserName('jdoe');
//        $user->setPlainPassword('123');
//        $this->userManager->create($user);
//        
//        $resource = new Resource();
//        $this->resourceManager->createResource($resource, $user);
//        
//        $conn = $this->em->getConnection();
//        
//        $sql = <<<SELECTCLAUSE
//        SELECT 
//            oid.object_identifier, sid.identifier, sid.username
//        FROM 
//            acl_entries e  
//        JOIN
//            acl_object_identities oid ON (
//            oid.id = e.object_identity_id
//        )   
//        JOIN
//            acl_security_identities sid ON (
//            sid.id = e.security_identity_id
//        )
//        JOIN 
//            acl_classes c ON (
//            c.id = oid.class_id
//        )
//        WHERE 
//            c.class_type = ?
//            AND
//            sid.username = ?
//            AND
//            sid.identifier = ?
//       GROUP BY
//            oid.object_identifier    
//SELECTCLAUSE;
//            
//        $stmt = $conn->prepare($sql);
//        $class = "Claroline\\ResourceBundle\\Entity\\Resource";
//        $username = true;
//        $identifier = "Claroline\\UserBundle\\Entity\\User-" . $user->getUsername();
//        $stmt->bindValue(1, $class);
//        $stmt->bindValue(2, $username);
//        $stmt->bindValue(3, $identifier);
//        $stmt->execute();
//        $resources = $stmt->fetchAll();
//                
//        $this->assertEquals(1, count($resources));
//    }
}