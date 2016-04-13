<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\HttpFoundation;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class XmlResponseTest extends MockeryTestCase
{
    private $encoder;

    public function setUp()
    {
        parent::setUp();

        $this->encoder = $this->mock('Symfony\Component\Serializer\Encoder\XmlEncoder');
    }

    public function testConstruct()
    {
        $response = new XmlResponse(array('key' => 'value'));
        $this->assertEquals('text/xml', $response->headers->get('content-type'));
        $this->assertContains('<response><key>value</key></response>', $response->getContent());
    }
}
