<?php

namespace Claroline\CoreBundle\Library\Installation\Settings;

use Doctrine\DBAL\DriverManager;

class DatabaseChecker
{
    const INVALID_DRIVER = 'invalid_driver';
    const NOT_BLANK_EXPECTED = 'not_blank_expected';
    const NUMBER_EXPECTED = 'positive_number_expected';
    const CANNOT_CONNECT_TO_SERVER = 'cannot_connect_to_db_server';
    const CANNOT_CONNECT_OR_CREATE = 'cannot_connect_to_or_create_database';
    const DATABASE_NOT_EMPTY = 'not_empty_database';

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

    public function getValidationErrors()
    {
        return $this->errors;
    }

    public function connectToDatabase()
    {
        if ($this->canConnect(true)) {
            if (!$this->isDatabaseEmpty()) {
                return static::DATABASE_NOT_EMPTY;
            }
        } elseif ($this->canConnect(false)) {
            if (!$this->canCreateDatabase()) {
                return static::CANNOT_CONNECT_OR_CREATE;
            }
        } else {
            return static::CANNOT_CONNECT_TO_SERVER;
        }

        return true;
    }

    private function validateSettings()
    {
        if (false !== $this->checkIsNotBlank('driver')) {
            if (!in_array($this->settings['driver'], $this->getDrivers())) {
                $this->errors['driver'] = static::INVALID_DRIVER;
            }
        }

        $this->checkIsNotBlank('host');
        $this->checkIsNotBlank('dbname');
        $this->checkIsNotBlank('user');
        $this->checkIsNotBlank('password');

        if (isset($this->settings['port']) && '' !== trim($this->settings['port'])) {
            if (!is_numeric($this->settings['port']) || (int) $this->settings['port'] < 0) {
                $this->errors['port'] = static::NUMBER_EXPECTED;
            }
        }

        return $this->errors;
    }

    private function checkIsNotBlank($option)
    {
        if (!isset($this->settings[$option]) || '' === trim($this->settings[$option])) {
            $this->errors[$option] = static::NOT_BLANK_EXPECTED;

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
        if (count($this->errors) !== 0) {
            throw new \Exception('Connection settings are not valid');
        }

        $parameters = $this->settings;
        $parameters['charset'] = 'UTF8';

        if (!$useDatabase) {
            unset($parameters['dbname']);
        }

        return DriverManager::getConnection($parameters);
    }

    private function isDatabaseEmpty()
    {
        if ($this->canConnect(true)) {
            $tables = $this->databaseConnection->getSchemaManager()->listTableNames();

            return count($tables) === 0;
        }

        throw new \Exception('Cannot connect to database');
    }

    private function canCreateDatabase()
    {
        if ($this->canConnect(false)) {
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
}
