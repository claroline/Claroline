<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Entity\Activity\ActivityRuleAction;

class Updater030000
{
    private $container;
    private $logger;
    private $om;
    
    public function __construct($container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->updateActivityRuleAction();
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    private function log($message)
    {
        if ($log = $this->logger) {
            $log('    ' . $message);
        }
    }
    
    private function updateActivityRuleAction()
    {
        $this->log('Updating list of action that can be mapped to an activity rule...');

        $fileType = $this->om
            ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findOneByName('file');
        $textType = $this->om
            ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findOneByName('text');

        $fileAction = new ActivityRuleAction();
        $fileAction->setAction('resource-read');
        $fileAction->setResourceType($fileType);
        $this->om->persist($fileAction);

        $textAction = new ActivityRuleAction();
        $textAction->setAction('resource-read');
        $textAction->setResourceType($textType);
        $this->om->persist($textAction);

        $this->om->flush();
    }
}
