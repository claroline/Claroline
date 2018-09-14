<?php

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResourceRightsIntegrityCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:resource_rights:check')
            ->setDescription('Checks the resource mask decoders integrity of the platform.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->conn = $this->getContainer()->get('doctrine.dbal.default_connection');
        $roles[] = $this->getContainer()->get('claroline.persistence.object_manager')->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_ANONYMOUS');
        $roles[] = $this->getContainer()->get('claroline.persistence.object_manager')->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_USER');

        foreach ($roles as $role) {
            $sql = "
                INSERT into claro_resource_rights (mask, role_Id, resourceNode_Id)
                SELECT 0, {$role->getId()}, node.id
                FROM claro_resource_node node
                WHERE node.id NOT IN (
                  SELECT node2.id from claro_resource_node node2
                  JOIN claro_resource_rights rights ON rights.resourceNode_Id = node2.id
                  JOIN claro_role role on rights.role_Id = {$role->getId()}
                )
            ";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        }
    }
}
