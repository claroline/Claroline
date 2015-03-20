<?php

namespace UJM\ExoBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/20 02:22:32
 */
class Version20150320142231 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_question ALTER title 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_question ALTER description 
            DROP NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_question ALTER title 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_question ALTER description 
            SET 
                NOT NULL
        ");
    }
}