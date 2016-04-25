<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Session;

use PDO;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

/**
 * @DI\Service("claroline.session.database_validator")
 */
class DatabaseSessionValidator
{
    public function validate(array $parameters)
    {
        $errors = array();

        if ($parameters['session_storage_type'] === 'pdo') {
            $dsn = $parameters['session_db_dsn'];
            $username = $parameters['session_db_user'];
            $password = $parameters['session_db_password'];

            try {
                $pdo = new PDO($dsn, $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $errors = $this->validateSchema($pdo, $parameters);

                if (count($errors) === 0) {
                    $errors = $this->testOperations($pdo, $parameters);
                }
            } catch (\PDOException $e) {
                $errors[] = 'database_connection_failed';
            }
        }

        return $errors;
    }

    private function validateSchema(PDO $connection, array $parameters)
    {
        $dbal = DriverManager::getConnection(array('pdo' => $connection));
        $schema = $dbal->getSchemaManager();

        if (!$schema->tablesExist(array($parameters['session_db_table']))) {
            return array('session_db_no_table');
        }

        $table = $schema->listTableDetails($parameters['session_db_table']);
        $expectedColumns = array(
            'id_col' => Type::STRING,
            'data_col' => Type::TEXT,
            'time_col' => Type::INTEGER,
        );
        $errors = array();

        foreach ($expectedColumns as $column => $type) {
            if (!$table->hasColumn($name = $parameters['session_db_'.$column])) {
                $errors[] = 'session_db_no_'.$column;
                continue;
            }

            if ($table->getColumn($name)->getType()->getName() !== $type) {
                $errors[] = 'session_db_invalid_type_'.$column;
            }
        }

        if (!$table->hasPrimaryKey() || $table->getPrimaryKeyColumns() !== array($parameters['session_db_id_col'])) {
            $errors[] = 'session_db_id_col_must_be_pk';
        }

        return $errors;
    }

    private function testOperations(PDO $connection, array $parameters)
    {
        $options = array(
            'db_table' => $parameters['session_db_table'],
            'db_id_col' => $parameters['session_db_id_col'],
            'db_data_col' => $parameters['session_db_data_col'],
            'db_time_col' => $parameters['session_db_time_col'],
        );
        $errors = array();
        $handler = new PdoSessionHandler($connection, $options);
        $connection->beginTransaction();

        try {
            $handler->write('session_1', 'session 1 data...');
            $handler->write('session_2', 'session 2 data...');
            $handler->read('session_1');
            $handler->read('session_2');
            $handler->destroy('session 1');
            $handler->gc(1);
        } catch (\Exception $ex) {
            $errors[] = 'session_db_cannot_perform_operations';
        }

        $connection->rollBack();

        return $errors;
    }
}
