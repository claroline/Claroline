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


class Updater021000
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
        $this->updateRights();
        $this->updateDefaultPerms();
    }

    public function updateRights()
    {

        $this->log('Removing ROLE_ADMIN from the resources rights...');
        //remove rights of ROLE_ADMIN.
        $roleAdmin = $this->om->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_ADMIN');
        $rights = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceRights')->findBy(array('role' => $roleAdmin));

        foreach ($rights as $right) {
            $this->om->remove($right);
        }

        $this->om->flush();

        $workspaces = $this->om->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->findAll();

        foreach ($workspaces as $workspace) {
            $managerRole = $this->container->get('claroline.manager.role_manager')->getManagerRole($workspace);

            //remove rights for managerRole
            $rights = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
                ->findBy(array('role' => $managerRole));

            foreach ($rights as $right) {
                if ($right->getWorkspace() === $workspace) {
                    $this->om->remove($right);
                }
            }

            //remove tools for managerRole
            $tools = $this->om->getRepository('ClarolineCoreBundle:Tool\OrderedTool')
                ->findBy(array('role' => $managerRole));

            foreach ($tools as $tool) {
                if ($tool->getWorkspace() === $tool) {
                    $this->om->remove($tool);
                }
            }
        }
    }

    public function updateDefaultPerms()
    {
        $tools = array(
            array('home', false, true),
            array('parameters', true, true),
            array('resource_manager', false, true),
            array('agenda', false, true),
            array('logs', false, true),
            array('analytics', false, true),
            array('users', false, true),
            array('badges', false, true)
        );

        foreach ($tools as $tool) {
            $entity = $this->om->getRepository('ClarolineCoreBunde:Tool\Tool')->findOneByName($tool[0]);
            $entity->setIsLockedForAdmin($tool[1]);
            $entity->setIsAnonymousExcluded($tool[2]);
        }
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