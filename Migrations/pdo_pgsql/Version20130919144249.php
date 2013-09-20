<?php

namespace Innova\PathBundle\Migrations\pdo_pgsql;

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
            ADD stepOrder INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP deployable
        ");
        $this->addSql("
            ALTER TABLE innova_step ALTER parent 
            DROP NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            ADD deployable BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP stepOrder
        ");
        $this->addSql("
            ALTER TABLE innova_step ALTER parent 
            SET 
                NOT NULL
        ");
    }
}