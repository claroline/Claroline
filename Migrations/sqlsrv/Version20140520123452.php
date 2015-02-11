<?php

namespace Innova\CollecticielBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/05/20 12:34:59
 */
class Version20140520123452 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_dropzone 
            ADD notify_on_drop BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_dropzone 
            ADD CONSTRAINT DF_6782FC23_1B468EE2 DEFAULT '0' FOR notify_on_drop
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_dropzone 
            DROP COLUMN notify_on_drop
        ");
    }
}