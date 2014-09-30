<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/23 03:30:14
 */
class Version20140923153012 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_message ALTER receiver_string TYPE VARCHAR(2047)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_message ALTER receiver_string TYPE VARCHAR(1023)
        ");
    }
}