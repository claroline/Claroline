<?php

namespace UJM\ExoBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/01/27 10:32:48
 */
class Version20140127103246 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_hole 
            ADD (
                selector NUMBER(1) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE ujm_hole MODIFY (
                position NUMBER(10) DEFAULT NULL, 
                orthography NUMBER(1) DEFAULT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_hole MODIFY (
                position NUMBER(10) NOT NULL, 
                orthography NUMBER(1) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE ujm_hole 
            DROP (selector)
        ");
    }
}