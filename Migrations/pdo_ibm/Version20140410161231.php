<?php

namespace UJM\ExoBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/04/10 04:12:34
 */
class Version20140410161231 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_exercise 
            ADD COLUMN published SMALLINT NOT NULL
        ");
        $this->addSql("
            UPDATE ujm_exercise SET published=TRUE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_exercise 
            DROP COLUMN published
        ");
    }
}