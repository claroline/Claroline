<?php

namespace Claroline\CoreBundle\Controller;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class MessageControllerTest extends MockeryTestCase
{
    public function setUp()
    {
        parent::setUp();

        $reflector = new \ReflectionClass('Claroline\CoreBundle\Controller\MessageController');

        //Now get all the properties from class A in to $properties array
        $properties = $reflector->getProperties();
        var_dump($properties);
        /*
        private $request;
        private $router;
        private $formFactory;
        private $messageManager;
        $this->MessageManager = m::mock('Claroline\CoreBundle\Repository\GroupRepository');
        $this->controller = new MessageController($this->userRepo, $this->messageRepo, $this->writer);*/
    }

    public function testCreate()
    {
        echo "test";
    }
}


