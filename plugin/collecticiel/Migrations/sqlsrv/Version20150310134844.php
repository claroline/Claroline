<?php

namespace Innova\CollecticielBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/03/10 01:48:47
 */
class Version20150310134844 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_comment_read (
                id INT IDENTITY NOT NULL, 
                comment_id INT NOT NULL, 
                user_id INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ');
        $this->addSql('
            CREATE INDEX IDX_83EB06B9F8697D13 ON innova_collecticielbundle_comment_read (comment_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_83EB06B9A76ED395 ON innova_collecticielbundle_comment_read (user_id)
        ');
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_comment (
                id INT IDENTITY NOT NULL, 
                document_id INT NOT NULL, 
                user_id INT NOT NULL, 
                commentText VARCHAR(MAX), 
                comment_date DATETIME2(6) NOT NULL, 
                PRIMARY KEY (id)
            )
        ');
        $this->addSql('
            CREATE INDEX IDX_A9CB9095C33F7837 ON innova_collecticielbundle_comment (document_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_A9CB9095A76ED395 ON innova_collecticielbundle_comment (user_id)
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
            DROP CONSTRAINT FK_1C357F0C4D224760
        ');
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_1C357F0C4D224760'
            ) 
            ALTER TABLE innova_collecticielbundle_document 
            DROP CONSTRAINT IDX_1C357F0C4D224760 ELSE 
            DROP INDEX IDX_1C357F0C4D224760 ON innova_collecticielbundle_document
        ");
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_document 
            DROP COLUMN drop_id
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_comment_read 
            DROP CONSTRAINT FK_83EB06B9F8697D13
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
