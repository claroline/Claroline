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
        //w/e
    }

    public function updateDefaultPerms()
    {
        //w/e
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