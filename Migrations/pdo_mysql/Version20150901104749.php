<?php

namespace Innova\CollecticielBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/09/01 10:47:51
 */
class Version20150901104749 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_document 
            ADD sender_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_document 
            ADD CONSTRAINT FK_1C357F0CF624B39D FOREIGN KEY (sender_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1C357F0CF624B39D ON innova_collecticielbundle_document (sender_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_document 
            DROP FOREIGN KEY FK_1C357F0CF624B39D
        ");
        $this->addSql("
            DROP INDEX IDX_1C357F0CF624B39D ON innova_collecticielbundle_document
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_document 
            DROP sender_id
        ");
    }
}