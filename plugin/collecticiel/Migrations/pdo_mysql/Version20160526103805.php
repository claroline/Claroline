<?php

namespace Innova\CollecticielBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/05/26 10:38:10
 */
class Version20160526103805 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_dropzone CHANGE maximum_notation maximum_notation SMALLINT DEFAULT 20 NOT NULL
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_notation 
            ADD recordOrTransmit TINYINT(1) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_dropzone CHANGE maximum_notation maximum_notation SMALLINT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_notation 
            DROP recordOrTransmit
        ');
    }
}
