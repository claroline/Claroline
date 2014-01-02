<?php

namespace Claroline\CoreBundle\Tests\Integration;

use Behat\MinkBundle\Test\MinkTestCase;

class LoginTest extends MinkTestCase
{
    protected $base;

    protected function setUp()
    {
        $this->base = $this->getKernel()->getContainer()->getParameter('mink.base_url');
    }

    public function testDisplayLoginPage()
    {
        $session = $this->getMink()->getSession('selenium2');
        $session->visit($this->base.'/login');
        var_dump($session->getPage()->getContent());
        $this->assertTrue($session->getPage()->hasContent('username'));
    }
}
