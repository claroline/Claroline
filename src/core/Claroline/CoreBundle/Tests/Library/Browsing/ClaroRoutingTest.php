<?php
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Library\Browsing\ClaroRouting; 

class ClaroRoutingTest extends TransactionalTestCase
{
     /** @var Claroline\CoreBundle\Library\Browsing\ClaroRouting */
    private $claroRouting;
    
    public function setUp()
    {
        parent::setUp();
        $this->claroRouting =  $this->client->getContainer()->get('claroline.routing');   
    }
    
    public function testGetRouteName()
    {
        $routeName = $this->claroRouting->getRouteName('CoreBundle', 'Administration', 'index');   
        $this->assertEquals($routeName, 'claro_admin_index');
    }
}


