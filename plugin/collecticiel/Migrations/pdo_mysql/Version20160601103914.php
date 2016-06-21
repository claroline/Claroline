<?php

namespace Innova\CollecticielBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/06/01 10:39:16
 */
class Version20160601103914 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_dropzone CHANGE maximum_notation maximum_notation SMALLINT DEFAULT 0
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_dropzone CHANGE maximum_notation maximum_notation SMALLINT DEFAULT 20
        ');
    }
}
