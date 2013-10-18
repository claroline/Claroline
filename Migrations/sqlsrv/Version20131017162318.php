<?php

namespace Innova\PathBundle\Migrations\sqlsrv;

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
            ALTER TABLE innova_nonDigitalResource ALTER COLUMN description VARCHAR(MAX)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_nonDigitalResource ALTER COLUMN description VARCHAR(MAX) NOT NULL
        ");
    }
}