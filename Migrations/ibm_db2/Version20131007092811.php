<?php

namespace Innova\PathBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/07 09:28:12
 */
class Version20131007092811 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_pathtemplate 
            DROP COLUMN \"user\" 
            DROP COLUMN edit_date ALTER description description CLOB(1M) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_pathtemplate 
            ADD COLUMN \"user\" VARCHAR(255) NOT NULL 
            ADD COLUMN edit_date TIMESTAMP(0) NOT NULL ALTER description description CLOB(1M) NOT NULL
        ");
    }
}