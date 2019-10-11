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

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use PDO;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

class DatabaseSessionValidator
{
    public function validate(array $parameters)
    {
        $errors = [];

        if ('pdo' === $parameters['session_storage_type']) {
            $dsn = $parameters['session_db_dsn'];
            $username = $parameters['session_db_user'];
            $password = $parameters['session_db_password'];

            try {
                $pdo = new PDO($dsn, $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $errors = $this->validateSchema($pdo, $parameters);

                if (0 === count($errors)) {
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
        $dbal = DriverManager::getConnection(['pdo' => $connection]);
        $schema = $dbal->getSchemaManager();

        if (!$schema->tablesExist([$parameters['session_db_table']])) {
            return ['session_db_no_table'];
        }

        $table = $schema->listTableDetails($parameters['session_db_table']);
        $expectedColumns = [
            'id_col' => Type::STRING,
            'data_col' => Type::TEXT,
            'time_col' => Type::INTEGER,
        ];
        $errors = [];

        foreach ($expectedColumns as $column => $type) {
            if (!$table->hasColumn($name = $parameters['session_db_'.$column])) {
                $errors[] = 'session_db_no_'.$column;
                continue;
            }

            if ($table->getColumn($name)->getType()->getName() !== $type) {
                $errors[] = 'session_db_invalid_type_'.$column;
            }
        }

        if (!$table->hasPrimaryKey() || $table->getPrimaryKeyColumns() !== [$parameters['session_db_id_col']]) {
            $errors[] = 'session_db_id_col_must_be_pk';
        }

        return $errors;
    }

    private function testOperations(PDO $connection, array $parameters)
    {
        $options = [
            'db_table' => $parameters['session_db_table'],
            'db_id_col' => $parameters['session_db_id_col'],
            'db_data_col' => $parameters['session_db_data_col'],
            'db_time_col' => $parameters['session_db_time_col'],
        ];
        $errors = [];
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
