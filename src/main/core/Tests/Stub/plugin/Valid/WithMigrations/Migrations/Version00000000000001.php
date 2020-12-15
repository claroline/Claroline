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
use Claroline\CoreBundle\Library\Installation\BundleMigration;

class Version00000000000001 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $table = $schema->createTable($this->getTablePrefix().'_stuffs');

        $this->addId($table);
        $table->addColumn(
            'name',
            'string',
            array(
                'length' => 50,
            )
        );
    }

    public function down(Schema $schema)
    {
        $schema->dropTable($this->getTablePrefix().'_stuffs');
    }
}
