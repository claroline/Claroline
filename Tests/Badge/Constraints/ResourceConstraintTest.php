<?php

namespace Claroline\CoreBundle\Badge\Constraints;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Log\Log;

class ResourceConstraintTest extends MockeryTestCase
{
    public function testValidateNoLog()
    {
        $badgeRule = new BadgeRule();

        $associatedLogs     = array();
        $resourceConstraint = new ResourceConstraint($badgeRule, $associatedLogs);

        $this->assertFalse($resourceConstraint->validate());
    }

    public function testValidateNotSameResource()
    {
        $resourceNode = new ResourceNode();
        $resourceNode->setId($resourceNodeId = rand(10, PHP_INT_MAX));

        $otherResourceNode = new ResourceNode();
        $otherResourceNode->setId($otherResourceNodeId = rand(0, 10));

        $badgeRule = new BadgeRule();
        $badgeRule->setResource($resourceNode);

        $log                    = new Log();
        $log->setResourceNode($otherResourceNode);

        $associatedLogs     = array($log);
        $resourceConstraint = new ResourceConstraint($badgeRule, $associatedLogs);

        $this->assertFalse($resourceConstraint->validate());
    }

    public function testValidateSameResource()
    {
        $resourceNode = new ResourceNode();
        $resourceNode->setId($resourceNodeId = rand(10, PHP_INT_MAX));

        $badgeRule = new BadgeRule();
        $badgeRule->setResource($resourceNode);

        $log                    = new Log();
        $log->setResourceNode($resourceNode);

        $associatedLogs     = array($log);
        $resourceConstraint = new ResourceConstraint($badgeRule, $associatedLogs);

        $this->assertTrue($resourceConstraint->validate());
    }
}
