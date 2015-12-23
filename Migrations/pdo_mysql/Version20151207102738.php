<?php

namespace Innova\CollecticielBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/12/07 10:27:42
 */
class Version20151207102738 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_dropzone 
            ADD evaluation TINYINT(1) DEFAULT '0' NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_dropzone 
            DROP evaluation
        ");
    }
}