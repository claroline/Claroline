<?php

namespace UJM\ExoBundle\Migrations\pdo_oci;

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
            ALTER TABLE ujm_question MODIFY (
                title VARCHAR2(255) DEFAULT NULL NULL, 
                description CLOB DEFAULT NULL NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_question MODIFY (
                title VARCHAR2(255) NOT NULL, 
                description CLOB NOT NULL
            )
        ");
    }
}