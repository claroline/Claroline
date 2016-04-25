<?php

namespace Innova\CollecticielBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/12/10 08:43:08
 */
class Version20151210084304 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_return_receipt_type (
                id INT AUTO_INCREMENT NOT NULL, 
                type_name LONGTEXT NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_return_receipt (
                id INT AUTO_INCREMENT NOT NULL, 
                document_id INT NOT NULL, 
                user_id INT NOT NULL, 
                dropzone_id INT NOT NULL, 
                return_receipt_type_id INT DEFAULT NULL, 
                return_receipt_date DATETIME NOT NULL, 
                INDEX IDX_78A1DB96C33F7837 (document_id), 
                INDEX IDX_78A1DB96A76ED395 (user_id), 
                INDEX IDX_78A1DB9654FC3EC3 (dropzone_id), 
                INDEX IDX_78A1DB96FD252543 (return_receipt_type_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_return_receipt 
            ADD CONSTRAINT FK_78A1DB96C33F7837 FOREIGN KEY (document_id) 
            REFERENCES innova_collecticielbundle_document (id)
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_return_receipt 
            ADD CONSTRAINT FK_78A1DB96A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_return_receipt 
            ADD CONSTRAINT FK_78A1DB9654FC3EC3 FOREIGN KEY (dropzone_id) 
            REFERENCES innova_collecticielbundle_dropzone (id)
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_return_receipt 
            ADD CONSTRAINT FK_78A1DB96FD252543 FOREIGN KEY (return_receipt_type_id) 
            REFERENCES innova_collecticielbundle_return_receipt_type (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_return_receipt 
            DROP FOREIGN KEY FK_78A1DB96FD252543
        ');
        $this->addSql('
            DROP TABLE innova_collecticielbundle_return_receipt_type
        ');
        $this->addSql('
            DROP TABLE innova_collecticielbundle_return_receipt
        ');
    }
}
