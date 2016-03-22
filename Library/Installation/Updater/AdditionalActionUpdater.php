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
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AdditionalActionUpdater extends Updater
{
    private $container;
    private $om;

    public function __construct(ContainerInterface $container, $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    public function postUpdate()
    {
        $manager = $this->container->get('claroline.manager.administration_manager');
        $manager->setLogger($this->logger);
        $manager->addDefaultAdditionalActions();
        $this->removeUserAdminActionTable();
    }

    private function removeUserAdminActionTable()
    {
        $schema = $this->container->get('doctrine.dbal.default_connection')->getSchemaManager();

        if ($schema->tablesExist('claro_user_admin_action')) {
            $this->log('Removing claro_user_admin_action table...');
            $schema->dropTable('claro_user_admin_action');
        }
    }
}
