<?php

namespace Claroline\CoreBundle\Library\Browsing;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HistoryBrowserTest extends WebTestCase
{
    /** @var HistoryBrowser */
    private $browser;

    /** @var Symfony\Component\HttpFoundation\Request */
    private $request;

    /** @var Symfony\Component\HttpFoundation\Session */
    private $session;

    /** @var Claroline\CoreBundle\Library\Testing\PlatformTestConfigurationHandler */
    private $configHandler;

    protected function setUp()
    {
        parent::setUp();
        $container = self::createClient()->getContainer();
        $this->session = $container->get('session');
        $this->configHandler = $container->get('claroline.config.platform_config_handler');
        $this->configHandler->setParameter('context_history_max_size', 4);
        $this->request = $this->getMockedRequest();
        $this->browser = new HistoryBrowser($this->request, $this->session, $this->configHandler);
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->configHandler->eraseTestConfiguration();
    }

    public function testBrowserInitsAnArraySessionVariableToHandleHistory()
    {
        $this->assertTrue($this->session->has(HistoryBrowser::HISTORY_SESSION_VARIABLE));
        $this->assertTrue(is_array($this->session->get(HistoryBrowser::HISTORY_SESSION_VARIABLE)));
        $this->assertTrue(is_array($this->browser->getContextHistory()));
    }

    public function testKeepCurrentContextIsOnlyAllowedForGetRequests()
    {
        $this->setExpectedException('RuntimeException');
        $browser = new HistoryBrowser($this->getMockedRequest('POST'), $this->session, $this->configHandler);
        $browser->keepCurrentContext('Some context name');
    }

    public function testKeepCurrentContextRequiresAValidContextName()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->browser->keepCurrentContext('');
    }

    public function testBrowserBuildsCompleteContextsAndReturnsThemFromTheNewerToTheOlder()
    {
        $browser = new HistoryBrowser($this->getMockedRequest('GET', 'some/uri/1'), $this->session, $this->configHandler);
        $browser->keepCurrentContext('A');
        $browser = new HistoryBrowser($this->getMockedRequest('GET', 'some/uri/2'), $this->session, $this->configHandler);
        $browser->keepCurrentContext('B');
        $browser = new HistoryBrowser($this->getMockedRequest('GET', 'some/uri/3'), $this->session, $this->configHandler);
        $browser->keepCurrentContext('C');

        $history = $this->browser->getContextHistory();

        $this->assertEquals(3, count($history));
        $this->assertEquals('C', $history[0]->getName());
        $this->assertEquals('B', $history[1]->getName());
        $this->assertEquals('A', $history[2]->getName());
        $this->assertEquals('some/uri/3', $history[0]->getUri());
        $this->assertEquals('some/uri/2', $history[1]->getUri());
        $this->assertEquals('some/uri/1', $history[2]->getUri());
    }

    public function testBrowserKeepsOnlyOneInstanceOfAGivenContext()
    {
        $this->browser->keepCurrentContext('A');
        $this->browser->keepCurrentContext('B');
        $this->browser->keepCurrentContext('A');
        $this->browser->keepCurrentContext('C');

        $history = $this->browser->getContextHistory();

        $this->assertEquals(3, count($history));
        $this->assertEquals('C', $history[0]->getName());
        $this->assertEquals('A', $history[1]->getName());
        $this->assertEquals('B', $history[2]->getName());
    }

    public function testBrowserDequeuesOlderElementWhenQueueMaxSizeIsReached()
    {
        $this->browser->keepCurrentContext('A');
        $this->browser->keepCurrentContext('B');
        $this->browser->keepCurrentContext('C');
        $this->browser->keepCurrentContext('D');
        $this->browser->keepCurrentContext('E');

        $history = $this->browser->getContextHistory();

        $this->assertEquals(4, count($history));
        $this->assertEquals('E', $history[0]->getName());
        $this->assertEquals('D', $history[1]->getName());
        $this->assertEquals('C', $history[2]->getName());
        $this->assertEquals('B', $history[3]->getName());
    }

    public function testBrowserTruncatesSessionStoredQueueIfSizeConfigParamHasChangedToSmaller()
    {
        $this->browser->keepCurrentContext('A');
        $this->browser->keepCurrentContext('B');
        $this->browser->keepCurrentContext('C');
        $this->browser->keepCurrentContext('D');

        $this->configHandler->setParameter('context_history_max_size', 2);
        $otherBrowserInstance = new HistoryBrowser($this->request, $this->session, $this->configHandler);
        $history = $otherBrowserInstance->getContextHistory();

        $this->assertEquals(2, count($history));
        $this->assertEquals('D', $history[0]->getName());
        $this->assertEquals('C', $history[1]->getName());
    }

    public function testGetLastContextReturnsNewerElementInHistoryIfAny()
    {
        $this->browser->keepCurrentContext('A');
        $this->browser->keepCurrentContext('B');

        $context = $this->browser->getLastContext();

        $this->assertEquals('B', $context->getName());
    }

    public function testGetLastContextReturnsNullIfNoContextAvailable()
    {
        $this->assertNull($this->browser->getLastContext());
    }

    private function getMockedRequest($method = 'GET', $uri = 'some/uri')
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue($method));
        $request->expects($this->any())
            ->method('getUri')
            ->will($this->returnValue($uri));

        return $request;
    }
}