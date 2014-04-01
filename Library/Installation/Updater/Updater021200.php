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

class Updater021200
{
    private $container;
    private $logger;
    private $om;
    private $conn;

    public function __construct($container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function postUpdate()
    {
        $this->updateUsers();
        $this->updateTools();
    }

    public function updateUsers()
    {
        $this->log('Updating users...');
        $users = $this->om->getRepository('ClarolineCoreBundle:User')->findAll();
        $this->om->startFlushSuite();
        $i = 0;

        foreach ($users as $user) {
            $user->setIsMailDisplayed(false);
            $this->om->persist($user);
            $i++;

            if ($i % 200 === 0) {
                $this->om->endFlushSuite();
                $this->om->startFlushSuite();
            }
        }

        $this->om->endFlushSuite();

        $this->log('Done.');
    }

    public function updateTools()
    {
        $myBadgesToolName = 'my_badges';
        $myBadgesTool = $this->om->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneByName($myBadgesToolName);

        if (null === $myBadgesTool) {
            $this->log('Creating new tool for displaying user badges in workspace...');
            $newBadgeTool = new Tool();
            $newBadgeTool
                ->setName($myBadgesToolName)
                ->setClass('icon-trophy')
                ->setIsWorkspaceRequired(false)
                ->setIsDesktopRequired(false)
                ->setDisplayableInWorkspace(true)
                ->setDisplayableInDesktop(false)
                ->setExportable(false)
                ->setIsConfigurableInWorkspace(false)
                ->setIsConfigurableInDesktop(false)
                ->setIsLockedForAdmin(false)
                ->setIsAnonymousExcluded(true);

            $this->om->persist($newBadgeTool);

            $this->log('New tool for displaying user badges in workspace created.');
        }

        $this->om->flush();
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