<?php

namespace Innova\CollecticielBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/05/12 04:01:20
 */
class Version20160512160118 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_notation (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                document_id INT NOT NULL, 
                dropzone_id INT NOT NULL, 
                note INT NOT NULL, 
                comment_text LONGTEXT DEFAULT NULL, 
                quality_text LONGTEXT DEFAULT NULL, 
                note_date DATETIME NOT NULL, 
                INDEX IDX_C63449EFA76ED395 (user_id), 
                INDEX IDX_C63449EFC33F7837 (document_id), 
                INDEX IDX_C63449EF54FC3EC3 (dropzone_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_notation 
            ADD CONSTRAINT FK_C63449EFA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_notation 
            ADD CONSTRAINT FK_C63449EFC33F7837 FOREIGN KEY (document_id) 
            REFERENCES innova_collecticielbundle_document (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_notation 
            ADD CONSTRAINT FK_C63449EF54FC3EC3 FOREIGN KEY (dropzone_id) 
            REFERENCES innova_collecticielbundle_dropzone (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE innova_collecticielbundle_notation
        ');
    }
}
