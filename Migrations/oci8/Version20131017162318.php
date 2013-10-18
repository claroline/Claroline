<?php

namespace Innova\PathBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/17 04:23:19
 */
class Version20131017162318 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_nonDigitalResource MODIFY (description CLOB DEFAULT NULL)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_nonDigitalResource MODIFY (description CLOB NOT NULL)
        ");
    }
}