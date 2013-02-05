<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class ParametersControllerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture(array('admin'));
    }

    public function testDesktopSwitchVisibility()
    {
        $repo = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Tool\Tool');

        $baseDisplayedTools = $repo->getDesktopTools($this->getFixtureReference('user/admin'));
        $nbBaseDisplayedTools = count($baseDisplayedTools);
        $home = $repo->findOneBy(array('name' => 'calendar'));
        $this->logUser($this->getFixtureReference('user/admin'));

        $this->client->request(
            'POST',
            "/desktop/tool/properties/invert/visibility/tool/{$home->getId()}"
        );

        $this->assertEquals(
            ++$nbBaseDisplayedTools,
            count($repo->getDesktopTools($this->getFixtureReference('user/admin')))
        );

        $this->client->request(
            'POST',
            "/desktop/tool/properties/invert/visibility/tool/{$home->getId()}"
        );

       $this->assertEquals(
            --$nbBaseDisplayedTools,
            count($repo->getDesktopTools($this->getFixtureReference('user/admin')))
        );
    }

    public function testDesktopMoveUp()
    {
        $home = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findOneBy(array('name' => 'home'));

        $parameters = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findOneBy(array('name' => 'parameters'));

        $this->logUser($this->getFixtureReference('user/admin'));

        $this->client->request(
            'POST',
            "/desktop/tool/properties/invert/visibility/tool/{$home->getId()}"
        );

        $this->client->request(
            'POST',
            "/desktop/tool/properties/move/up/tool/{$home->getId()}"
        );

        $repo = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Tool\DesktopTool');

        $this->assertEquals(
            1,
            $repo->findOneBy(array('tool' => $home, 'user' => $this->getFixtureReference('user/admin')))
                ->getOrder()
        );

        $this->assertEquals(
            2,
            $repo->findOneBy(array('tool' => $parameters, 'user' => $this->getFixtureReference('user/admin')))
               ->getOrder()
        );
    }
}

