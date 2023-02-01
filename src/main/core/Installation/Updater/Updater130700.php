<?php

namespace Claroline\CoreBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

class Updater130700 extends Updater
{
    /** @var Connection */
    private $connection;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var ObjectManager */
    private $om;

    public function __construct(
        Connection $connection,
        PlatformConfigurationHandler $config,
        ObjectManager $om
    ) {
        $this->connection = $connection;
        $this->config = $config;
        $this->om = $om;
    }

    public function preUpdate(): void
    {
        $this->updateLocales();
        $this->updateIconSets(); // not in ThemeBundle because it will be too late (plugin configuration is processed before preUpdate)
    }

    public function postUpdate(): void
    {
        $this->removeAdminTool('dashboard');
        $this->removeTool('dashboard');
    }

    private function updateLocales(): void
    {
        $removed = ['de', 'es', 'it', 'nl'];

        // replace default platform locale
        if (in_array($this->config->getParameter('locales.default'), $removed)) {
            $this->config->setParameter('locales.default', 'en');
        }

        // replace available locales
        $availableLocales = [];
        foreach ($this->config->getParameter('locales.available') as $locale) {
            if (!in_array($locale, $removed)) {
                $availableLocales[] = $locale;
            }
        }

        $this->config->setParameter('locales.available', $availableLocales);

        // replaces users locales
        $this->connection->executeQuery('UPDATE claro_user SET locale = ? WHERE locale IN (?)',
            ['fr', $removed],
            [ParameterType::STRING, Connection::PARAM_STR_ARRAY]
        );
    }

    private function updateIconSets(): void
    {
        // delete old set
        $this->connection->executeQuery('DELETE FROM claro_icon_set WHERE cname = "claroline"');
        // replace current set by the new one
        $this->config->setParameter('display.resource_icon_set', 'claroline');
    }

    private function removeAdminTool(string $toolName): void
    {
        $taskTool = $this->om->getRepository(AdminTool::class)->findOneBy([
            'name' => $toolName,
        ]);

        if ($taskTool) {
            // let's cascades remove all related records
            $this->om->remove($taskTool);
            $this->om->flush();
        }
    }

    private function removeTool(string $toolName): void
    {
        $tool = $this->om->getRepository(Tool::class)->findOneBy([
            'name' => $toolName,
        ]);

        if ($tool) {
            // let's cascades remove all related records
            $this->om->remove($tool);
            $this->om->flush();
        }
    }
}
