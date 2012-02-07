<?php

namespace Claroline\CoreBundle\Installation\Plugin\Validator;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\CoreBundle\Plugin\ClarolinePlugin;

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

    /**
     * @dataProvider clarolinePluginProvider
     */
    public function testValidatorCallsCommonCheckerForEveryPluginType(ClarolinePlugin $plugin)
    {
        $checkers = $this->getMockedCheckers();
        $checkers['common']->expects($this->once())
             ->method('check')
             ->with($plugin);

        $this->validator->setCommonChecker($checkers['common']);
        $this->validator->validate($plugin);
    }
    
    /**
     * @dataProvider clarolinePluginAndTypeProvider
     */
    public function testValidatorCallsDedicatedCheckerForSpecificPluginType($type, ClarolinePlugin $plugin)
    {
        $checkers = $this->getMockedCheckers();
        $checker = $checkers[$type];
        $checker->expects($this->once())
             ->method('check')
             ->with($plugin);

        $setMethod = 'set' . ucfirst($type) . 'Checker';
        $this->validator->{$setMethod}($checker);
        $this->validator->validate($plugin);
    }
    
    public function clarolinePluginProvider()
    {
        $plugins = $this->getMockedPlugins();
        
        return array(
            array($plugins['extension']),
            array($plugins['tool'])
        );
    }
    
    public function clarolinePluginAndTypeProvider()
    {
        $plugins = $this->getMockedPlugins();
        
        return array(
            array('extension', $plugins['extension']),
            array('tool', $plugins['tool'])
        );
    }
    
    private function getMockedCheckers()
    {
        $checkers = array();
        $checkers['common'] = $this->getMockBuilder('Claroline\CoreBundle\Installation\Plugin\Validator\Checker\CommonChecker')
            ->disableOriginalConstructor()
            ->getMock();
        $checkers['extension'] = $this->getMockBuilder('Claroline\CoreBundle\Installation\Plugin\Validator\Checker\ExtensionChecker')
            ->disableOriginalConstructor()
            ->getMock();
        $checkers['tool'] = $this->getMockBuilder('Claroline\CoreBundle\Installation\Plugin\Validator\Checker\ToolChecker')
            ->disableOriginalConstructor()
            ->getMock();
        
        return $checkers;
    }
    
    private function getMockedPlugins()
    {
        $plugins = array();
        $plugins['extension'] = $this->getMock('Claroline\CoreBundle\Plugin\ClarolineExtension');
        $plugins['tool'] = $this->getMock('Claroline\CoreBundle\Plugin\ClarolineTool');
        
        return $plugins;
    }
}