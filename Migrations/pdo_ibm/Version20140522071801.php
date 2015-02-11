<?php

namespace Innova\CollecticielBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/05/22 07:18:07
 */
class Version20140522071801 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_drop 
            ADD COLUMN unlocked_drop SMALLINT NOT NULL 
            ADD COLUMN unlocked_user SMALLINT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_dropzone 
            DROP COLUMN notify_on_drop
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_drop 
            DROP COLUMN unlocked_drop 
            DROP COLUMN unlocked_user
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_dropzone 
            ADD COLUMN notify_on_drop SMALLINT NOT NULL
        ");
    }
}