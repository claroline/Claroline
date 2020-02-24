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

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120532 extends Updater
{
    /** @var PlatformConfigurationHandler */
    private $config;

    /** @var Connection */
    private $conn;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->conn = $container->get('doctrine.dbal.default_connection');
        $this->config = $container->get(PlatformConfigurationHandler::class);
    }

    public function postUpdate()
    {
        $this->resetEvaluations();
        $this->updatePlatformName();
    }

    private function resetEvaluations()
    {
        $this->log('Reset incorrect data for user evaluations...');

        // 01/03/2019
        $stmt = $this->conn->prepare('
            UPDATE claro_workspace_evaluation SET duration = 0 WHERE evaluation_date > "2019-03-01"
        ');
        $stmt->execute();

        $stmt = $this->conn->prepare('
            UPDATE claro_resource_evaluation SET duration = 0 WHERE evaluation_date > "2019-03-01"
        ');
        $stmt->execute();

        $stmt = $this->conn->prepare('
            UPDATE claro_resource_user_evaluation SET duration = 0 WHERE evaluation_date > "2019-03-01"
        ');
        $stmt->execute();
        $stmt = $this->conn->prepare('
            UPDATE claro_resource_user_evaluation SET nb_attempts = 0, nb_openings = 0
        ');
        $stmt->execute();
    }

    private function updatePlatformName()
    {
        $url = $this->config->getParameter('internet.platform_url');
        if (!empty($url) && false === strpos('http', $url)) {
            if ($this->config->getParameter('ssl.enabled')) {
                $this->config->setParameter('internet.platform_url', 'https://'.$url);
            } else {
                $this->config->setParameter('internet.platform_url', 'http://'.$url);
            }
        }
    }
}
