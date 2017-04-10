<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/03/30 03:18:51
 */
class Version20170330151850 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE ujm_color_selection (
                id INT AUTO_INCREMENT NOT NULL, 
                selection_id INT DEFAULT NULL, 
                color_id INT DEFAULT NULL, 
                score DOUBLE PRECISION NOT NULL, 
                feedback LONGTEXT DEFAULT NULL, 
                INDEX IDX_97921969E48EFE78 (selection_id), 
                INDEX IDX_979219697ADA1FB5 (color_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_selection (
                id INT AUTO_INCREMENT NOT NULL, 
                interation_selection_id INT DEFAULT NULL, 
                begin INT NOT NULL, 
                end INT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                score DOUBLE PRECISION NOT NULL, 
                feedback LONGTEXT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_C93913FFD17F50A6 (uuid), 
                INDEX IDX_C93913FF4EA83EF1 (interation_selection_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_color (
                id INT AUTO_INCREMENT NOT NULL, 
                interaction_selection_id INT DEFAULT NULL, 
                colorCode VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_AADB06B4D17F50A6 (uuid), 
                INDEX IDX_AADB06B43CCAFA48 (interaction_selection_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_interaction_selection (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                text LONGTEXT NOT NULL, 
                mode VARCHAR(255) NOT NULL, 
                tries INT NOT NULL, 
                penalty DOUBLE PRECISION DEFAULT NULL, 
                UNIQUE INDEX UNIQ_7B1E8B31E27F6BF (question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE ujm_color_selection 
            ADD CONSTRAINT FK_97921969E48EFE78 FOREIGN KEY (selection_id) 
            REFERENCES ujm_selection (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_color_selection 
            ADD CONSTRAINT FK_979219697ADA1FB5 FOREIGN KEY (color_id) 
            REFERENCES ujm_color (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_selection 
            ADD CONSTRAINT FK_C93913FF4EA83EF1 FOREIGN KEY (interation_selection_id) 
            REFERENCES ujm_interaction_selection (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_color 
            ADD CONSTRAINT FK_AADB06B43CCAFA48 FOREIGN KEY (interaction_selection_id) 
            REFERENCES ujm_interaction_selection (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_selection 
            ADD CONSTRAINT FK_7B1E8B31E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_color_selection 
            DROP FOREIGN KEY FK_97921969E48EFE78
        ');
        $this->addSql('
            ALTER TABLE ujm_color_selection 
            DROP FOREIGN KEY FK_979219697ADA1FB5
        ');
        $this->addSql('
            ALTER TABLE ujm_selection 
            DROP FOREIGN KEY FK_C93913FF4EA83EF1
        ');
        $this->addSql('
            ALTER TABLE ujm_color 
            DROP FOREIGN KEY FK_AADB06B43CCAFA48
        ');
        $this->addSql('
            DROP TABLE ujm_color_selection
        ');
        $this->addSql('
            DROP TABLE ujm_selection
        ');
        $this->addSql('
            DROP TABLE ujm_color
        ');
        $this->addSql('
            DROP TABLE ujm_interaction_selection
        ');
    }
}
