<?php

namespace Innova\CollecticielBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2016/02/05 08:57:33
 */
class Version20160205085732 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_dropzone 
            DROP evaluation_type
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_dropzone 
            ADD evaluation_type VARCHAR(255) DEFAULT 'noEvaluation' NOT NULL COLLATE utf8_unicode_ci
        ");
    }
}