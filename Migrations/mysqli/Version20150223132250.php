<?php

namespace UJM\ExoBundle\Migrations\mysqli;

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
            ADD position_force TINYINT(1) DEFAULT NULL, 
            ADD ordre INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction_matching 
            ADD shuffle TINYINT(1) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_proposal 
            ADD position_force TINYINT(1) DEFAULT NULL, 
            ADD ordre INT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_interaction_matching 
            DROP shuffle
        ");
        $this->addSql("
            ALTER TABLE ujm_label 
            DROP position_force, 
            DROP ordre
        ");
        $this->addSql("
            ALTER TABLE ujm_proposal 
            DROP position_force, 
            DROP ordre
        ");
    }
}