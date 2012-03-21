<?php

use Claroline\CoreBundle\Library\Testing\SeleniumSauceTestCase;

/**
 * @group seleniumSauce
 */
class SeleniumSauceTest extends SeleniumSauceTestCase
{
    public function test() 
    {
        $this->open('/claronext/web/app_dev.php');
        $this->waitForTextPresent('Claroline');
        $this->assertTextNotPresent('Yahoo');
        //$this->type('element_id', 'Some text');
        //$this->click('submit');
        //$this->click('link=some link');
    }
}