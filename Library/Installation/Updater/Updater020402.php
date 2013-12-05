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

use Claroline\CoreBundle\Entity\Tool\Tool;

class Updater020402
{
    private $container;
    private $logger;

    public function __construct($container)
    {
        $this->container = $container;
    }


    public function postUpdate()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $decoder = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneBy(array('name' => 'analytics'));
        if (!$decoder) {
            $wsTool = new Tool();
            $wsTool->setName('analytics');
            $wsTool->setClass('icon-bar-chart');
            $wsTool->setIsWorkspaceRequired(false);
            $wsTool->setIsDesktopRequired(false);
            $wsTool->setDisplayableInWorkspace(true);
            $wsTool->setDisplayableInDesktop(false);
            $wsTool->setExportable(false);
            $wsTool->setIsConfigurableInWorkspace(false);
            $wsTool->setIsConfigurableInDesktop(false);

            $em->persist($wsTool);
            $this->log("Adding 'analytics' tool in workspaces");
        } else {
            $this->log("The 'analytics' tool already exists");
        }

        $em->flush();
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
}