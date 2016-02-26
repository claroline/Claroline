<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ResultBundle\Manager;

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\ResultBundle\Entity\Result;

class ResultManagerTest extends TransactionalTestCase
{
    /** @var ResultManager */
    private $manager;
    private $om;

    protected function setUp()
    {
        parent::setUp();
        $container = $this->client->getContainer();
        $this->manager = $container->get('claroline.result.result_manager');
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function testCreateAndDelete()
    {
        $repo = $this->om->getRepository('ClarolineResultBundle:Result');
        $result = $this->manager->create(new Result());
        $results = $repo->findAll();
        $this->assertEquals(1, count($results));
        $this->assertEquals($result, $results[0]);
        $this->manager->delete($results[0]);
        $this->assertEquals(0, count($repo->findAll()));
    }

    public function testWidget()
    {
        $content = $this->manager->getWidgetContent(new WidgetInstance());
        $this->assertNotEmpty($content);
    }
}
