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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\DataFixtures\PostInstall\Data\LoadAdminHomeData;
use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120502 extends Updater
{
    /** @var ContainerInterface */
    private $container;
    private $conn;
    /** @var ObjectManager */
    private $om;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->container = $container;
        $this->conn = $container->get('doctrine.dbal.default_connection');
        $this->om = $this->container->get('Claroline\AppBundle\Persistence\ObjectManager');
    }

    public function postUpdate()
    {
        $this->fixesDirectoriesPageSizes();
        $this->addDefaultAdminHome();
    }

    private function fixesDirectoriesPageSizes()
    {
        // 50 and 100 are old values from incorrect migration.
        // it blocks the directory validation
        $stmt = $this->conn->prepare('
            UPDATE claro_directory 
            SET availablePageSizes = REPLACE(REPLACE(availablePageSizes, "100", "120"), "50", "60") 
            WHERE availablePageSizes LIKE "%50%" OR availablePageSizes LIKE "%100%"
        ');
        $stmt->execute();
    }

    private function addDefaultAdminHome()
    {
        $tabs = $this->om->getRepository(HomeTab::class)->findBy(['type' => HomeTab::TYPE_ADMIN]);

        if (0 === count($tabs)) {
            $this->log('Creating default admin home tab...');

            $fixtures = new LoadAdminHomeData();
            $fixtures->setContainer($this->container);
            $fixtures->load($this->om);
        }
    }
}
