<?php

namespace HeVinci\CompetencyBundle\Util;

class UnitTestCase extends \PHPUnit_Framework_TestCase
{
    protected function mock($class)
    {
        return $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }
} 