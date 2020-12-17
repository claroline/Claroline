<?php

namespace HeVinci\CompetencyBundle\Util;

use PHPUnit\Framework\TestCase;

abstract class UnitTestCase extends TestCase
{
    protected function mock($class)
    {
        return $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
