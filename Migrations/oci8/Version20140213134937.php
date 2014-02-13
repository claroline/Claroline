<?php

namespace UJM\ExoBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/02/13 01:49:38
 */
class Version20140213134937 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_interaction_hole 
            ADD (
                htmlWithoutValue CLOB DEFAULT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_interaction_hole 
            DROP (htmlWithoutValue)
        ");
    }
}