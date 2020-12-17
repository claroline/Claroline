<?php

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResourceRightsIntegrityCommand extends Command
{
    private $conn;
    private $om;
    private $rightsManager;

    public function __construct(Connection $conn, ObjectManager $om, RightsManager $rightsManager)
    {
        $this->conn = $conn;
        $this->om = $om;
        $this->rightsManager = $rightsManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Checks the resource mask decoders integrity of the platform.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $roleRepository = $this->om->getRepository('ClarolineCoreBundle:Role');
        $roles[] = $roleRepository->findOneByName('ROLE_ANONYMOUS');
        $roles[] = $roleRepository->findOneByName('ROLE_USER');

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

        $resourceNodeRepository = $this->om->getRepository(ResourceNode::class);

        while ($row = $ids->fetch()) {
            $node = $resourceNodeRepository->find($row['id']);
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

                    $this->rightsManager->editPerms($perms, $role, $node);
                }
            } else {
                $this->rightsManager->copy($node->getParent(), $node);
            }
            ++$i;
        }

        return 0;
    }
}
