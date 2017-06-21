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
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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

        //set the default claroline user
        $defaultUser = $container->get('claroline.manager.user_manager')->getDefaultUser();
        $token = new UsernamePasswordToken($defaultUser, '123', 'main', $defaultUser->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
    }

    public function postUpdate()
    {
        $this->createDefaultModel();
        $roleManager = $this->container->get('claroline.manager.role_manager');
        $om = $this->container->get('claroline.persistence.object_manager');
        $models = $this->connection->query('SELECT * FROM claro_workspace_model')->fetchAll();
        $toCheck = [];
        $i = 0;
        $this->connection->query('SET FOREIGN_KEY_CHECKS=0');

        foreach ($models as $model) {
            $code = '[MOD]'.$model['name'];
            $workspace = $om->getRepository('ClarolineCoreBundle:Workspace\Workspace')->findOneByCode($code);

            if (!$workspace) {
                ++$i;
                $this->log('Creating workspace from model '.$model['name'].': '.$i.'/'.count($models));

                try {
                    $modelUsers = $this->connection->query("SELECT * FROM claro_workspace_model_user u where u.workspacemodel_id = {$model['id']}")->fetchAll();
                    $modelGroups = $this->connection->query("SELECT * FROM claro_workspace_model_group g where g.workspacemodel_id = {$model['id']}")->fetchAll();
                    $modelResources = $this->connection->query("SELECT * FROM claro_workspace_model_resource r where r.model_id = {$model['id']}")->fetchAll();
                    $baseWorkspace = $om->getRepository('ClarolineCoreBundle:Workspace\Workspace')->find($model['workspace_id']);

                    $userIds = array_unique(array_map(function ($data) {
                        return $data['user_id'];
                    }, $modelUsers));
                    $groupIds = array_unique(array_map(function ($data) {
                        return $data['group_id'];
                    }, $modelGroups));
                    $nodeIds = array_unique(array_map(function ($data) {
                        return $data['resource_node_id'];
                    }, $modelResources));

                    $users = $om->findByIds('Claroline\CoreBundle\Entity\User', $userIds);
                    $groups = $om->findByIds('Claroline\CoreBundle\Entity\User', $groupIds);
                    $nodes = $om->findByIds('Claroline\CoreBundle\Entity\Resource\ResourceNode', $nodeIds);
                    if (count($users) === 0) {
                        $users[0] = $this->connection->get('claroline.manager.user_manager')->getDefaultUser();
                    }
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
                    $this->om->forceFlush();
                    $this->om->clear();
                    $defaultUser = $this->container->get('claroline.manager.user_manager')->getDefaultUser();
                    $token = new UsernamePasswordToken($defaultUser, '123', 'main', $defaultUser->getRoles());
                    $this->container->get('security.token_storage')->setToken($token);
                    $this->om->merge($defaultUser);

                    $this->log("Updatig cursus for model {$model['name']}");
                    try {
                        $modelResources = $this->connection->query("
                          UPDATE claro_cursusbundle_course r
                          SET r.workspace_model_id = {$newWorkspace->getId()}
                          WHERE r.workspace_model_id = {$model['id']}"
                        )->execute();
                    } catch (ForeignKeyConstraintViolationException $e) {
                        $this->log('Error when setting the model flag for cursuses', LogLevel::ERROR);
                        $this->log($e->getMessage(), LogLevel::ERROR);
                        $this->log('Continuing update...');
                        $toCheck[] = [$newWorkspace, $model];
                    }
                } catch (TableNotFoundException $e) {
                    $this->log('Model table already removed');
                }
            } else {
                $this->log('Workspace already exists');
            }
        }

        $this->connection->query('SET FOREIGN_KEY_CHECKS=1');
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
