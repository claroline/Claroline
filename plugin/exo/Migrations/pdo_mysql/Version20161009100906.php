<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/10/09 10:09:09
 */
class Version20161009100906 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic 
            DROP FOREIGN KEY FK_9EBD442FEE45BDBF
        ');
        $this->addSql('
            DROP INDEX IDX_9EBD442FEE45BDBF ON ujm_interaction_graphic
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic CHANGE picture_id image_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic 
            ADD CONSTRAINT FK_9EBD442F3DA5256D FOREIGN KEY (image_id) 
            REFERENCES ujm_picture (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_9EBD442F3DA5256D ON ujm_interaction_graphic (image_id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_hole CHANGE htmlWithoutValue htmlWithoutValue LONGTEXT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_question 
            ADD mime_type VARCHAR(255) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic 
            DROP FOREIGN KEY FK_9EBD442F3DA5256D
        ');
        $this->addSql('
            DROP INDEX IDX_9EBD442F3DA5256D ON ujm_interaction_graphic
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic CHANGE image_id picture_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic 
            ADD CONSTRAINT FK_9EBD442FEE45BDBF FOREIGN KEY (picture_id) 
            REFERENCES ujm_picture (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_9EBD442FEE45BDBF ON ujm_interaction_graphic (picture_id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_hole CHANGE htmlWithoutValue htmlWithoutValue LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('
            ALTER TABLE ujm_question 
            DROP mime_type
        ');
    }
}
