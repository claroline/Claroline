<?php

namespace UJM\ExoBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/23 01:22:52
 */
class Version20150223132250 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_label 
            ADD position_force BIT
        ");
        $this->addSql("
            ALTER TABLE ujm_label 
            ADD ordre INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction_matching 
            ADD shuffle BIT
        ");
        $this->addSql("
            ALTER TABLE ujm_proposal 
            ADD position_force BIT
        ");
        $this->addSql("
            ALTER TABLE ujm_proposal 
            ADD ordre INT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_interaction_matching 
            DROP COLUMN shuffle
        ");
        $this->addSql("
            ALTER TABLE ujm_label 
            DROP COLUMN position_force
        ");
        $this->addSql("
            ALTER TABLE ujm_label 
            DROP COLUMN ordre
        ");
        $this->addSql("
            ALTER TABLE ujm_proposal 
            DROP COLUMN position_force
        ");
        $this->addSql("
            ALTER TABLE ujm_proposal 
            DROP COLUMN ordre
        ");
    }
}