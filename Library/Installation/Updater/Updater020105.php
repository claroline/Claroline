<?php

namespace Claroline\CoreBundle\Library\Installation\Updater;

class Updater020105
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
        $this->log("Adding the 'ROLE_USER' right to every resource...");
        $rightsRepo = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights');
        $resourceRepo = $em->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
        $nodes = $resourceRepo->findAll();
        $roleUser = $em->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_USER');
        $start = ($rightsRepo->findOneBy(array('resourceNode' => $nodes[0], 'role' => $roleUser))) ? false: true;
        $rightsManager = $this->container->get('claroline.manager.rights_manager');

        if ($start) {
            foreach ($nodes as $node) {
                $rightsManager->create(
                    0,
                    $roleUser,
                    $node,
                    false,
                    array()
                );
            }
        } else {
            $this->log("The ROLE_USER was already added...");
        }

        $this->log("Done.");
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
