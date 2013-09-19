<?php

namespace Innova\PathBundle\Migrations\pdo_sqlsrv;

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
            DROP COLUMN deployable
        ");
        $this->addSql("
            ALTER TABLE innova_step ALTER COLUMN parent NVARCHAR(255)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            ADD deployable BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN stepOrder
        ");
        $this->addSql("
            ALTER TABLE innova_step ALTER COLUMN parent NVARCHAR(255) NOT NULL
        ");
    }
}