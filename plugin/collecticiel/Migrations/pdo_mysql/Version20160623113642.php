<?php

namespace Innova\CollecticielBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/06/23 11:36:45
 */
class Version20160623113642 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_grading_notation (
                id INT AUTO_INCREMENT NOT NULL, 
                dropzone_id INT NOT NULL, 
                notation_name LONGTEXT NOT NULL, 
                INDEX IDX_7B8D8C4954FC3EC3 (dropzone_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_choice_notation (
                id INT AUTO_INCREMENT NOT NULL, 
                choice_text LONGTEXT DEFAULT NULL, 
                notation_id INT NOT NULL, 
                INDEX IDX_9ABE6A929680B7F7 (notation_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_grading_notation 
            ADD CONSTRAINT FK_7B8D8C4954FC3EC3 FOREIGN KEY (dropzone_id) 
            REFERENCES innova_collecticielbundle_dropzone (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE innova_collecticielbundle_grading_notation
        ');
        $this->addSql('
            DROP TABLE innova_collecticielbundle_choice_notation
        ');
    }
}
