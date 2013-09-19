<?php

namespace Innova\PathBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/19 02:42:49
 */
class Version20130919144249 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            ADD stepOrder INT NOT NULL, 
            DROP deployable, 
            CHANGE parent parent VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            ADD deployable TINYINT(1) NOT NULL, 
            DROP stepOrder, 
            CHANGE parent parent VARCHAR(255) NOT NULL
        ");
    }
}