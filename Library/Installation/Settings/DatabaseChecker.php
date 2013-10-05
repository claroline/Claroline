<?php

namespace Claroline\CoreBundle\Library\Installation\Settings;

use Doctrine\DBAL\DriverManager;

class DatabaseChecker
{
    private $settings;
    private $errors;
    private $serverConnection;
    private $databaseConnection;

    public function __construct(array $connectionSettings)
    {
        $this->settings = $connectionSettings;
        $this->errors = array();
        $this->validateSettings();
    }

    public function areSettingsValid()
    {
        return count($this->errors) === 0;
    }

    public function getValidationErrors()
    {
        return $this->errors;
    }

    public function canConnectToServer()
    {
        return $this->canConnect(false);
    }

    public function canConnectToDatabase()
    {
        return $this->canConnect(true);
    }

    public function isDatabaseEmpty()
    {
        if ($this->canConnectToDatabase()) {
            $tables = $this->databaseConnection->getSchemaManager()->listTableNames();

            return count($tables) === 0;
        }

        throw new \Exception('Cannot connect to database');
    }

    public function canCreateDatabase()
    {
        if ($this->canConnectToServer()) {
            try {
                $this->serverConnection->getSchemaManager()->createDatabase(
                    $this->settings['dbname']
                );

                return true;
            } catch (\Exception $ex) {
                return false;
            }
        }

        throw new \Exception('Cannot connect to database server');
    }

    private function validateSettings()
    {
        if (false !== $this->checkIsNotBlank('driver')) {
            if (!in_array($this->settings['driver'], $this->getDrivers())) {
                $this->errors['driver'] = 'invalid_driver';
            }
        }

        $this->checkIsNotBlank('host');
        $this->checkIsNotBlank('dbname');
        $this->checkIsNotBlank('user');
        $this->checkIsNotBlank('password');

        if (isset($this->settings['port']) && '' !== trim($this->settings['port'])) {
            if (!is_numeric($this->settings['port']) || (int) $this->settings['port'] < 0) {
                $this->errors['port'] = 'number_expected';
            }
        }

        return $this->errors;
    }

    private function checkIsNotBlank($option)
    {
        if (!isset($this->settings[$option]) || '' === trim($this->settings[$option])) {
            $this->errors[$option] = 'not_blank_expected';

            return false;
        }

        return true;
    }

    private function getDrivers()
    {
        return array('pdo_mysql', 'pdo_pgsql');
    }

    private function canConnect($useDatabase)
    {
        if ($this->databaseConnection && $useDatabase || $this->serverConnection && !$useDatabase) {
            return true;
        }

        $connection = $this->getConnection($useDatabase);

        try {
            if ($connection->connect()) {
                if ($useDatabase) {
                    $this->databaseConnection = $connection;
                } else {
                    $this->serverConnection = $connection;
                }

                return true;
            }

            return false;
        } catch (\PDOException $ex) {
            return false;
        }
    }

    private function getConnection($useDatabase)
    {
        if (!$this->areSettingsValid()) {
            throw new \Exception('Connection settings are not valid');
        }

        $parameters = $this->settings;
        $parameters['charset'] = 'UTF8';

        if (!$useDatabase) {
            unset($parameters['dbname']);
        }

        return DriverManager::getConnection($parameters);
    }
}
