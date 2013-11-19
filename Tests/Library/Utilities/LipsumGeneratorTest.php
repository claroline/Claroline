<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Utilities;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class LipsumGeneratorTest extends MockeryTestCase
{
    private $lipsumGenerator;

    public function setUp()
    {
        parent::setUp();

        $this->lipsumGenerator = new LipsumGenerator();
    }

    public function testGenerateLipsumWithLowMaxCharSize()
    {
        $text = $this->lipsumGenerator->generateLipsum(250, true, 128);

        $this->assertGreaterThan(110, strlen($text));
        $this->assertGreaterThan(strlen($text), 128);
    }
}
