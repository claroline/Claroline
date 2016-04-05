<?php

namespace HeVinci\CompetencyBundle;

use HeVinci\CompetencyBundle\Util\UnitTestCase;

class HeVinciCompetencyBundleTest extends UnitTestCase
{
    public function testGetInstaller()
    {
        $bundle = new HeVinciCompetencyBundle();
        $this->assertInstanceOf(
            'HeVinci\CompetencyBundle\Installation\AdditionalInstaller',
            $bundle->getAdditionalInstaller()
        );
    }
}
