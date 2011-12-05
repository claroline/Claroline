<?php

namespace Claroline\PluginBundle\Repository;

use Claroline\PluginBundle\Widget\ApplicationLauncher;

class ApplicationLauncherTest extends \PHPUnit_Framework_TestCase
{
    private $validRouteId;
    private $validTranslationKey;
    private $validAccessControl;
    
    public function setUp()
    {
        $this->validRouteId = 'route_test';
        $this->validTranslationKey = 'translation_test';
        $this->validAccessControl = array('ROLE_TEST');
    }
    
    public function testNoExceptionIsThrownWithValidArguments()
    {
        new ApplicationLauncher(
            $this->validRouteId,
            $this->validTranslationKey,
            $this->validAccessControl
        );
    }
    
    /**
     * @dataProvider invalidRouteIdProvider
     */
    public function testConstructorThrowsAnExceptionOnInvalidRouteIdArgument($routeId)
    {
        $this->setExpectedException('Claroline\CommonBundle\Exception\ClarolineException');
        
        new ApplicationLauncher($routeId, $this->validTranslationKey, $this->validAccessControl);
    }
    
    /**
     * @dataProvider invalidTranslationKeyProvider
     */
    public function testConstructorThrowsAnExceptionOnInvalidTranslationKeyArgument($key)
    {
        $this->setExpectedException('Claroline\CommonBundle\Exception\ClarolineException');
        
        new ApplicationLauncher($this->validRouteId, $key, $this->validAccessControl);
    }
    
    /**
     * @dataProvider invalidAccessControlProvider
     */
    public function testConstructorThrowsAnExceptionOnInvalidAccessControlArgument($accessControl)
    {
        $this->setExpectedException('Claroline\CommonBundle\Exception\ClarolineException');
        
        new ApplicationLauncher($this->validRouteId, $this->validTranslationKey, $accessControl);
    }
    
    public function invalidRouteIdProvider()
    {
        return array(
            array(123),
            array(''),
            array($this->getTooLongString()),
        );
    }
    
    public function invalidTranslationKeyProvider()
    {
        return array(
            array(null),
            array(''),
            array($this->getTooLongString()),
        );
    }
    
    public function invalidAccessControlProvider()
    {
        return array(
            array(array())
        );
    }
    
    private function getTooLongString()
    {
        $tooLongString = '';
        
        for ($i = 0; $i < 100; ++$i)
        {
            $tooLongString .= 'XXXX';
        }
        
        return $tooLongString;
    }
}