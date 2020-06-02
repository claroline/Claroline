<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120545 extends Updater
{
    /** @var Connection */
    private $conn;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function postUpdate()
    {
        $this->fixTimezone();
    }

    private function fixTimezone()
    {
        $this->log('Updates dropzone dates timezone...');

        $toUpdate = [
            'claro_dropzonebundle_dropzone' => ['drop_start_date', 'drop_end_date', 'review_start_date', 'review_end_date'],
            'claro_dropzonebundle_drop' => ['drop_date'],
            'claro_dropzonebundle_document' => ['drop_date'],
            'claro_dropzonebundle_drop_comment' => ['creation_date', 'edition_date'],
            'claro_dropzonebundle_revision' => ['creation_date'],
            'claro_dropzonebundle_correction' => ['start_date', 'last_edition_date', 'end_date'],
        ];

        foreach ($toUpdate as $tableName => $columns) {
            foreach ($columns as $column) {
                $this->log(sprintf('Updates table "%s" and column "%s"...', $tableName, $column));

                $this->conn
                    ->prepare("
                        UPDATE {$tableName} SET {$column} = DATE_SUB({$column}, INTERVAL 2 HOUR) WHERE {$column} IS NOT NULL
                    ")
                    ->execute();
            }
        }
    }
}
