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
use Claroline\CoreBundle\Persistence\ObjectManager;

class Updater021101
{
    private $container;
    private $logger;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function __construct($container)
    {
        $this->container     = $container;
        $this->objectManager = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->log('Updating existing badges tool...');
        /** @var \Claroline\CoreBundle\Entity\Tool\Tool $badgesTool */
        $badgesTool = $this->objectManager->getRepository('Claroline\CoreBundle\Entity\Tool\Tool')->findOneByName('badges');
        $badgesTool->setDisplayName('badges_management');

        $this->objectManager->persist($badgesTool);

        $this->log('Existing badges tool updated.');

        $myBadgesToolName = 'my_badges';
        $myBadgesTool = $this->objectManager->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneByName($myBadgesToolName);

        if (null === $myBadgesTool) {
            $this->log('Creating new tool for displaying user badges in workspace...');
            $newBadgeTool = new Tool();
            $newBadgeTool
                ->setName($myBadgesToolName)
                ->setClass('icon-trophy')
                ->setDisplayName('badges')
                ->setIsWorkspaceRequired(false)
                ->setIsDesktopRequired(false)
                ->setDisplayableInWorkspace(true)
                ->setDisplayableInDesktop(false)
                ->setExportable(false)
                ->setIsConfigurableInWorkspace(false)
                ->setIsConfigurableInDesktop(false)
                ->setIsLockedForAdmin(false)
                ->setIsAnonymousExcluded(true);

            $this->objectManager->persist($newBadgeTool);

            $this->log('New tool for displaying user badges in workspace created.');
        }

        $this->objectManager->flush();
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