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

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater030502 extends Updater
{
    private $om;
    private $roleManager;
    private $userManager;

    public function __construct(ContainerInterface $container)
    {
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->roleManager = $container->get('claroline.manager.role_manager');
        $this->userManager = $container->get('claroline.manager.user_manager');
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function postUpdate()
    {
        $this->createPersonalRoleForUsers();
        $this->addMissingResourceModelTable();
    }

    private function addMissingResourceModelTable()
    {
        try {
            $this->log('creating claro_workspace_model_resource');
            $this->conn->query('CREATE TABLE claro_workspace_model_resource (id INT AUTO_INCREMENT NOT NULL, resource_node_id INT NOT NULL, model_id INT NOT NULL, isCopy TINYINT(1) NOT NULL, INDEX IDX_F5D706351BAD783F (resource_node_id), INDEX IDX_F5D706357975B7E7 (model_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
            $this->conn->query('ALTER TABLE claro_workspace_model_resource ADD CONSTRAINT FK_F5D706351BAD783F FOREIGN KEY (resource_node_id) REFERENCES claro_resource_node (id) ON DELETE CASCADE');
            $this->conn->query('ALTER TABLE claro_workspace_model_resource ADD CONSTRAINT FK_F5D706357975B7E7 FOREIGN KEY (model_id) REFERENCES claro_workspace_model (id) ON DELETE CASCADE;');
        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->log('claro_workspace_model_resource could not be created or already exists');
        }
    }

    private function createPersonalRoleForUsers()
    {
        $this->log('Creating personal role for each user ...');

        $users = $this->userManager->getUsersWithoutUserRole();

        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $this->roleManager->createUserRole($user);
        }
        $this->om->endFlushSuite();
    }
}
