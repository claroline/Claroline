<?php

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
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
        $om = $this->getContainer()->get('claroline.persistence.object_manager');

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

        //I really tried to make something clean but I eventually gave up. This request works but I wasn't able to make it functional with the query builder
        //This is a really slow request I think
        $sql = '
            SELECT id from claro_resource_node node WHERE node.id NOT IN (
            SELECT DISTINCT c0_.id FROM claro_resource_node c0_ INNER JOIN claro_resource_type c1_ ON c0_.resource_type_id = c1_.id INNER JOIN claro_workspace c2_ ON c0_.workspace_id = c2_.id LEFT JOIN claro_resource_rights c3_ ON c0_.id = c3_.resourceNode_id LEFT JOIN claro_role c4_ ON c3_.role_id = c4_.id
            INNER JOIN claro_workspace c6_ ON c4_.workspace_id = c6_.id
            GROUP BY c0_.id)
        ';

        $ids = $this->conn->query($sql);

        $output->writeln('Found '.$ids->rowCount().' resources with no right.');
        $i = 1;

        while ($row = $ids->fetch()) {
            $node = $om->getRepository(ResourceNode::class)->find($row['id']);
            $workspace = $node->getWorkspace();
            $output->writeln('Restore '.$i.'/'.$ids->rowCount());

            if (!$workspace) {
                $output->writeln('No workspace found for resource '.$node->getName().': '.$node->getId());
                continue;
            }

            if (!$node->getParent()) {
                $roles = $workspace->getRoles();

                foreach ($roles as $role) {
                    $perms = [];

                    if ($role->getId() === $workspace->getDefaultRole()->getId()) {
                        $perms = ['open' => true, 'export' => true];
                    }

                    $this->getContainer()->get('claroline.manager.rights_manager')->editPerms($perms, $role, $node);
                }
            } else {
                $this->getContainer()->get('claroline.manager.rights_manager')->copy($node->getParent(), $node);
            }
            ++$i;
        }
    }
}
