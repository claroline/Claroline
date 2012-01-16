<?php

namespace Claroline\CommonBundle\History;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\CommonBundle\History\Browser;

class BrowserTest extends WebTestCase
{
    /** @var Claroline\CommonBundle\History\Browser */
    private $browser;
    
    /** @var Symfony\Component\HttpFoundation\Request */
    private $request;
    
    /** @var Symfony\Component\HttpFoundation\Session */
    private $session;
    
    public function setUp()
    {
        $this->session = self::createClient()->getContainer()->get('session');
        $this->request = $this->getMockedRequest();
        $this->browser = new Browser($this->request, $this->session);
    }
    
    public function testKeepCurrentContextRequiresAValidKey()
    {
        $this->setExpectedException('Claroline\CommonBundle\Exception\ClarolineException');
        $this->request->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('GET'));
        $this->browser->keepCurrentContext('');
    }
    
    private function getMockedRequest()
    {
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        return $request;
    }
}