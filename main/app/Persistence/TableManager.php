<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Persistence;

use Claroline\BundleRecorder\Log\LoggableTrait;
use Doctrine\Common\Persistence\ObjectManager as ObjectManagerInterface;

class TableManager
{
    use LoggableTrait;

    /**
     * ObjectManager constructor.
     *
     * @param ObjectManagerInterface $om
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function copy($tableName)
    {
        $tablesList = $this->connection->getSchemaManager()->listTableNames();
        $tempName = $tableName.'_temp';

        if (!in_array($tableName, $tablesList)) {
            $this->log('Table '.$tableName.' does not exists');
        }

        if (!in_array($tempName, $tablesList)) {
            $this->log("backing up {$tableName} table...");
            try {
                $query = "
                    CREATE TABLE {$tempName}
                    AS (SELECT * FROM {$tableName})
                ";
                $this->connection->query($query);
            } catch (\Exception $e) {
                $this->log("{$tableName} doesn't exist");
            }
        } else {
            $this->log("{$tempName} table already exists");
        }
    }

    public function delete()
    {
    }
}
