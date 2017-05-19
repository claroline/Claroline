<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 3/1/17
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater090300 extends Updater
{
    private $container;
    private $workspaceManager;
    private $orgaManager;
    protected $logger;
    private $fileSystem;
    private $iconSetsDir;

    public function __construct(ContainerInterface $container, $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->workspaceManager = $this->container->get('claroline.manager.workspace_manager');
        $this->workspaceManager->setLogger($logger);
        $this->orgaManager = $this->container->get('claroline.manager.organization.organization_manager');
        $this->orgaManager->setLogger($logger);
        $this->fileSystem = $container->get('filesystem');
        $this->iconSetsDir = $container->getParameter('claroline.param.icon_sets_directory');
        $this->connection = $this->container->get('doctrine.dbal.default_connection');
        $this->om = $this->container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->createDefaultModel();
        $roleManager = $this->container->get('claroline.manager.role_manager');
        $om = $this->container->get('claroline.persistence.object_manager');
        $models = $this->connection->query('SELECT * FROM claro_workspace_model')->fetchAll();

        foreach ($models as $model) {
            $code = '[MOD]'.$model['name'].uniqid();
            $workspace = $om->getRepository('ClarolineCoreBundle:Workspace\Workspace')->findOneByCode($code);

            if (!$workspace) {
                $this->log('Creating workspace from model '.$model['name']);

                try {
                    $modelUsers = $this->connection->query("SELECT * FROM claro_workspace_model_user u where u.workspacemodel_id = {$model['id']}")->fetchAll();
                    $modelGroups = $this->connection->query("SELECT * FROM claro_workspace_model_group g where g.workspacemodel_id = {$model['id']}")->fetchAll();
                    $modelResources = $this->connection->query("SELECT * FROM claro_workspace_model_resource r where r.model_id = {$model['id']}")->fetchAll();
                    $baseWorkspace = $om->getRepository('ClarolineCoreBundle:Workspace\Workspace')->find($model['workspace_id']);

                    $userIds = array_map(function ($data) {
                        return $data['user_id'];
                    }, $modelUsers);
                    $groupIds = array_map(function ($data) {
                        return $data['group_id'];
                    }, $modelGroups);
                    $nodeIds = array_map(function ($data) {
                        return $data['resource_node_id'];
                    }, $modelResources);

                    $users = $om->findByIds('Claroline\CoreBundle\Entity\User', $userIds);
                    $groups = $om->findByIds('Claroline\CoreBundle\Entity\User', $groupIds);
                    $nodes = $om->findByIds('Claroline\CoreBundle\Entity\Resource\ResourceNode', $nodeIds);
                    $user = $users[0];

                    $newWorkspace = new Workspace();
                    $newWorkspace->setName($model['name']);
                    $newWorkspace->setCode($code);
                    $this->workspaceManager->createWorkspace($newWorkspace);
                    $this->workspaceManager->duplicateWorkspaceOptions($baseWorkspace, $newWorkspace);
                    $this->workspaceManager->duplicateWorkspaceRoles($baseWorkspace, $newWorkspace, $user);
                    $this->workspaceManager->duplicateOrderedTools($baseWorkspace, $newWorkspace);
                    $baseRoot = $this->workspaceManager->duplicateRoot($baseWorkspace, $newWorkspace, $user);

                    $this->workspaceManager->duplicateResources(
                      $nodes,
                      $this->workspaceManager->getArrayRolesByWorkspace($baseWorkspace),
                      $user,
                      $baseRoot
                    );

                    $newWorkspace->setIsModel(true);
                    $managerRole = $roleManager->getManagerRole($newWorkspace);
                    $roleManager->associateRoleToMultipleSubjects($users, $managerRole);
                    $roleManager->associateRoleToMultipleSubjects($groups, $managerRole);
                    $this->om->persist($newWorkspace);
                    $this->om->flush();

                    $this->log("Updatig cursus for model {$model['name']}");
                    $modelResources = $this->connection->query("
                      UPDATE claro_cursusbundle_course r
                      SET r.workspace_model_id = {$newWorkspace->getId()}
                      WHERE r.workspace_model_id = {$model['id']}"
                    )->execute();
                } catch (TableNotFoundException $e) {
                    $this->log('Model table already removed');
                }
            } else {
                $this->log('Workspace already exists');
            }
        }

        $this->dropModelTable();
    }

    public function createDefaultModel()
    {
        $this->log('Building default model...');
        $this->workspaceManager->getDefaultModel();
        $this->workspaceManager->getDefaultModel(true);
    }

    public function dropModelTable()
    {
        $tables = [
          'claro_workspace_model_user',
          'claro_workspace_model_group',
          'claro_workspace_model_resource',
          'claro_workspace_model_home_tab',
          'claro_workspace_model',
        ];

        $this->log('Drop useless model tables...');

        foreach ($tables as $table) {
            $this->log('Dropping '.$table);
            try {
                $this->connection->query('DROP TABLE '.$table);
            } catch (\Exception $e) {
                $this->log('Table already removed');
            }
        }
    }
}
