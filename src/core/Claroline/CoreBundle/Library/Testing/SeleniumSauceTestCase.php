<?php

namespace Claroline\CoreBundle\Library\Testing;

/**
 * @runTestsInParallel 10
 */
abstract class SeleniumSauceTestCase extends \PHPUnit_Extensions_SeleniumTestCase_SauceOnDemandTestCase 
{
    public static $browsers = array(
      array(
        'name' => 'Sauce Ondemand PHPUnit example (FF 7)',
        'browser' => 'firefox',
        'os' => 'Windows 2003',
        'browserVersion' => '7',
      ),
      array(
        'name' => 'Sauce Ondemand PHPUnit example (IE 9)',
        'browser' => 'iexplore',
        'os' => 'Windows 2008',
        'browserVersion' => '9',
      ),
      array(
        'name' => 'Sauce Ondemand PHPUnit example (Safari 4)',
        'browser' => 'safari',
        'os' => 'Windows 2003',
        'browserVersion' => '4',
      )
    );

    protected function setUp() 
    {
        $this->setBrowserUrl("http://localhost");
    }
}