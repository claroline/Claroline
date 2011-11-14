<?php

namespace Claroline\ResourceBundle\Service\Manager;

use Claroline\ResourceBundle\Entity\Resource;
use Claroline\UserBundle\Entity\User;
use Claroline\CommonBundle\Library\Testing\TransactionalTestCase;

class ResourceManagerTest extends TransactionalTestCase
{
    /** @var Doctrine\ORM\EntityManager */
    private $em;
    private $resourceManager;
    private $userManager;
    
    public function setUp()
    {
        parent :: setUp();
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->resourceManager = $this->client->getContainer()->get('claroline.resource.manager');
        $this->userManager = $this->client->getContainer()->get('claroline.user.manager');
    }
    
    public function testCreateResourceGivesPassedInUserOwnerPermissions()
    {
        $user = new User();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setUserName('jdoe');
        $user->setPlainPassword('123');
        $this->userManager->create($user);
        
        $resource = new Resource();
        $resource->setContent('Test content');
        $this->resourceManager->createResource($resource, $user);

        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->filter('input[id=_submit]')->form();
        $form['_username'] = 'jdoe';
        $form['_password'] = '123';
        $this->client->submit($form);
        
        $securityContext = $this->client->getContainer()->get('security.context');      
        $this->assertTrue($securityContext->isGranted('OWNER', $resource));
    }
    
    public function test() // cf RightManager::getAllowedEntityIdsByUser
    {
        $user = new User();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setUserName('jdoe');
        $user->setPlainPassword('123');
        $this->userManager->create($user);
        
        $resource = new Resource();
        $resource->setContent('Test content');
        $this->resourceManager->createResource($resource, $user);
        
        $conn = $this->em->getConnection();
        
        $sql = <<<SELECTCLAUSE
        SELECT 
            oid.object_identifier, sid.identifier, sid.username
        FROM 
            acl_entries e  
        JOIN
            acl_object_identities oid ON (
            oid.id = e.object_identity_id
        )   
        JOIN
            acl_security_identities sid ON (
            sid.id = e.security_identity_id
        )
        JOIN 
            acl_classes c ON (
            c.id = oid.class_id
        )
        WHERE 
            c.class_type = ?
            AND
            sid.username = ?
            AND
            sid.identifier = ?
       GROUP BY
            oid.object_identifier    
SELECTCLAUSE;
            
        $stmt = $conn->prepare($sql);
        $class = "Claroline\\ResourceBundle\\Entity\\Resource";
        $username = true;
        $identifier = "Claroline\\UserBundle\\Entity\\User-" . $user->getUsername();
        $stmt->bindValue(1, $class);
        $stmt->bindValue(2, $username);
        $stmt->bindValue(3, $identifier);
        $stmt->execute();
        $resources = $stmt->fetchAll();
                
        $this->assertEquals(1, count($resources));
    }
}