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
        $this->updateDefaultPerms();
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
            $entity = $this->om->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneByName($tool[0]);

            if ($entity) {
                $entity->setIsLockedForAdmin($tool[1]);
                $entity->setIsAnonymousExcluded($tool[2]);
                $this->om->persist($entity);
            }
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