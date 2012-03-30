<?php

namespace Claroline\CoreBundle\Library\Manager;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Entity\Resource\Directory;

class ResourceManagerTest extends FunctionalTestCase
{
    /** @var Claroline\CoreBundle\Manager\ResourceManager */
    private $resourceManager;
    
    protected function setUp()
    {
        parent::setUp();
        $this->resourceManager = $this->client->getContainer()->get('claroline.resource.manager');
        $this->loadUserFixture();
    }
    
    public function testCreateResourceGivesPassedInUserOwnerPermissions()
    {
        $resource = new Directory();
        $resource->setName('Test');
        $user = $this->getFixtureReference('user/user');
        $this->resourceManager->createResource($resource, $user);
        
        $this->logUser($user);   
        
        $this->assertTrue($this->getSecurityContext()->isGranted('OWNER', $resource));
    }
}