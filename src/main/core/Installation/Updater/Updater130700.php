<?php

namespace Claroline\CoreBundle\Installation\Updater;

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

    public function __construct(
        Connection $connection,
        PlatformConfigurationHandler $config
    ) {
        $this->connection = $connection;
        $this->config = $config;
    }

    public function preUpdate()
    {
        $this->updateLocales();
    }

    private function updateLocales()
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
}
