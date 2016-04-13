<?php

namespace Innova\CollecticielBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/12/08 11:19:13
 */
class Version20151208111910 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_return_recept_type (
                id INT AUTO_INCREMENT NOT NULL, 
                return_receipt_id INT NOT NULL, 
                type_name LONGTEXT NOT NULL, 
                INDEX IDX_A32DC238E0C802A4 (return_receipt_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_return_recept (
                id INT AUTO_INCREMENT NOT NULL, 
                document_id INT NOT NULL, 
                user_id INT NOT NULL, 
                dropzone_id INT NOT NULL, 
                return_receipt_id INT NOT NULL, 
                return_receipt_date DATETIME NOT NULL, 
                INDEX IDX_3515291BC33F7837 (document_id), 
                INDEX IDX_3515291BA76ED395 (user_id), 
                INDEX IDX_3515291B54FC3EC3 (dropzone_id), 
                INDEX IDX_3515291BE0C802A4 (return_receipt_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_return_recept_type 
            ADD CONSTRAINT FK_A32DC238E0C802A4 FOREIGN KEY (return_receipt_id) 
            REFERENCES innova_collecticielbundle_return_recept (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_return_recept 
            ADD CONSTRAINT FK_3515291BC33F7837 FOREIGN KEY (document_id) 
            REFERENCES innova_collecticielbundle_document (id)
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_return_recept 
            ADD CONSTRAINT FK_3515291BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_return_recept 
            ADD CONSTRAINT FK_3515291B54FC3EC3 FOREIGN KEY (dropzone_id) 
            REFERENCES innova_collecticielbundle_dropzone (id)
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_return_recept 
            ADD CONSTRAINT FK_3515291BE0C802A4 FOREIGN KEY (return_receipt_id) 
            REFERENCES innova_collecticielbundle_return_recept_type (id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_return_recept 
            DROP FOREIGN KEY FK_3515291BE0C802A4
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_return_recept_type 
            DROP FOREIGN KEY FK_A32DC238E0C802A4
        ');
        $this->addSql('
            DROP TABLE innova_collecticielbundle_return_recept_type
        ');
        $this->addSql('
            DROP TABLE innova_collecticielbundle_return_recept
        ');
    }
}
