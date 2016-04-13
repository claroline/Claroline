<?php

namespace Innova\CollecticielBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/03/10 01:48:46
 */
class Version20150310134844 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_comment_read (
                id INT AUTO_INCREMENT NOT NULL, 
                comment_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_83EB06B9F8697D13 (comment_id), 
                INDEX IDX_83EB06B9A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_comment (
                id INT AUTO_INCREMENT NOT NULL, 
                document_id INT NOT NULL, 
                user_id INT NOT NULL, 
                commentText LONGTEXT DEFAULT NULL, 
                comment_date DATETIME NOT NULL, 
                INDEX IDX_A9CB9095C33F7837 (document_id), 
                INDEX IDX_A9CB9095A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_comment_read 
            ADD CONSTRAINT FK_83EB06B9F8697D13 FOREIGN KEY (comment_id) 
            REFERENCES innova_collecticielbundle_comment (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_comment_read 
            ADD CONSTRAINT FK_83EB06B9A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_comment 
            ADD CONSTRAINT FK_A9CB9095C33F7837 FOREIGN KEY (document_id) 
            REFERENCES innova_collecticielbundle_document (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_comment 
            ADD CONSTRAINT FK_A9CB9095A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_document 
            DROP FOREIGN KEY FK_1C357F0C4D224760
        ');
        $this->addSql('
            DROP INDEX IDX_1C357F0C4D224760 ON innova_collecticielbundle_document
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_document 
            DROP drop_id
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_comment_read 
            DROP FOREIGN KEY FK_83EB06B9F8697D13
        ');
        $this->addSql('
            DROP TABLE innova_collecticielbundle_comment_read
        ');
        $this->addSql('
            DROP TABLE innova_collecticielbundle_comment
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_document 
            ADD drop_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_document 
            ADD CONSTRAINT FK_1C357F0C4D224760 FOREIGN KEY (drop_id) 
            REFERENCES innova_collecticielbundle_drop (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_1C357F0C4D224760 ON innova_collecticielbundle_document (drop_id)
        ');
    }
}
