<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Valid\WithMigrations\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version00000000000001 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->createTable($this->getTablePrefix().'_stuffs');

        $this->addId($table);
        $table->addColumn(
            'name',
            'string',
            [
                'length' => 50,
            ]
        );
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable($this->getTablePrefix().'_stuffs');
    }
}
