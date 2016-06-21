<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Settings;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\DBALException;

class DatabaseChecker
{
    const CANNOT_CONNECT_TO_SERVER = 'cannot_connect_to_db_server';
    const CANNOT_CONNECT_OR_CREATE = 'cannot_connect_to_or_create_database';
    const DATABASE_NOT_EMPTY = 'not_empty_database';

    private $settings;
    private $serverConnection;
    private $databaseConnection;

    public function __construct(DatabaseSettings $settings)
    {
        $this->settings = $settings;
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
        } catch (DBALException $ex) {
            return false;
        }
    }

    private function getConnection($useDatabase)
    {
        if (!$this->settings->isValid()) {
            throw new \Exception('Connection settings must be validated first');
        }

        $parameters = array(
            'driver' => $this->settings->getDriver(),
            'host' => $this->settings->getHost(),
            'user' => $this->settings->getUser(),
            'password' => $this->settings->getPassword(),
            'port' => $this->settings->getPort(),
            'charset' => $this->settings->getCharset(),
        );

        if ($useDatabase) {
            $parameters['dbname'] = $this->settings->getName();
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
                $this->serverConnection->getSchemaManager()->createDatabase($this->settings->getName());

                return true;
            } catch (\Exception $ex) {
                return false;
            }
        }

        throw new \Exception('Cannot connect to database server');
    }
}
