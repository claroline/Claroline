<?php

namespace UJM\ExoBundle\Migrations\oci8;

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
            ADD (
                position_force NUMBER(1) DEFAULT NULL NULL, 
                ordre NUMBER(10) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction_matching 
            ADD (
                shuffle NUMBER(1) DEFAULT NULL NULL
            )
        ");
        $this->addSql("
            ALTER TABLE ujm_proposal 
            ADD (
                position_force NUMBER(1) DEFAULT NULL NULL, 
                ordre NUMBER(10) NOT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_interaction_matching 
            DROP (shuffle)
        ");
        $this->addSql("
            ALTER TABLE ujm_label 
            DROP (position_force, ordre)
        ");
        $this->addSql("
            ALTER TABLE ujm_proposal 
            DROP (position_force, ordre)
        ");
    }
}