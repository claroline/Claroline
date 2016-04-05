<?php

namespace Icap\WikiBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/31 08:59:47
 */
class Version20131031085945 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__wiki 
            ADD COLUMN \"mode\" SMALLINT DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__wiki 
            DROP COLUMN \"mode\"
        ");
    }
}