<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\CoreBundle\Library\Plugin\ClarolinePlugin;

class ValidatorTest extends WebTestCase
{
    /** @var Validator */
    private $validator;

    protected function setUp()
    {
        $this->validator = self::createClient()->getContainer()->get('claroline.plugin.validator');
        $checkers = $this->getMockedCheckers();
        $this->validator->setCommonChecker($checkers['common']);
        $this->validator->setExtensionChecker($checkers['extension']);
        $this->validator->setToolChecker($checkers['tool']);
    }

    public function testValidatorCallsCommonCheckerForEveryPluginType()
    {
        $plugins = array(
            $this->getMock('Claroline\CoreBundle\Library\Plugin\ClarolineExtension'),
            $this->getMock('Claroline\CoreBundle\Library\Plugin\ClarolineTool')
        );

        foreach ($plugins as $plugin) {
            $checkers = $this->getMockedCheckers();
            $checkers['common']->expects($this->once())
                ->method('check')
                ->with($plugin);
            $this->validator->setCommonChecker($checkers['common']);
            $this->validator->validate($plugin);
        }
    }

    public function testValidatorCallsDedicatedCheckerForSpecificPluginType()
    {
        $plugins = array(
            'extension' => $this->getMock('Claroline\CoreBundle\Library\Plugin\ClarolineExtension'),
            'tool' => $this->getMock('Claroline\CoreBundle\Library\Plugin\ClarolineTool')
        );

        foreach ($plugins as $type => $plugin) {
            $checkers = $this->getMockedCheckers();
            $checker = $checkers[$type];
            $checker->expects($this->once())
                ->method('check')
                ->with($plugin);
            $setMethod = 'set' . ucfirst($type) . 'Checker';
            $this->validator->{$setMethod}($checker);
            $this->validator->validate($plugin);
        }
    }

    private function getMockedCheckers()
    {
        $checkers = array();
        $checkers['common'] = $this->getMockBuilder('Claroline\CoreBundle\Library\Installation\Plugin\CommonChecker')
            ->disableOriginalConstructor()
            ->getMock();
        $checkers['extension'] = $this->getMockBuilder('Claroline\CoreBundle\Library\Installation\Plugin\ExtensionChecker')
            ->disableOriginalConstructor()
            ->getMock();
        $checkers['tool'] = $this->getMockBuilder('Claroline\CoreBundle\Library\Installation\Plugin\ToolChecker')
            ->disableOriginalConstructor()
            ->getMock();

        return $checkers;
    }
}