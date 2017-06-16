<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/02/20 09:50:38
 */
class Version20170220095034 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE ujm_question_grid (
                id INT AUTO_INCREMENT NOT NULL,
                question_id INT DEFAULT NULL,
                sumMode VARCHAR(255) NOT NULL,
                rows INT NOT NULL,
                columns INT NOT NULL,
                borderWidth INT NOT NULL,
                borderColor VARCHAR(255) NOT NULL,
                penalty DOUBLE PRECISION NOT NULL,
                UNIQUE INDEX UNIQ_2412DE371E27F6BF (question_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_cell_choice (
                id INT AUTO_INCREMENT NOT NULL,
                cell_id INT DEFAULT NULL,
                response VARCHAR(255) NOT NULL,
                caseSensitive TINYINT(1) DEFAULT NULL,
                expected TINYINT(1) DEFAULT NULL,
                score DOUBLE PRECISION NOT NULL,
                feedback LONGTEXT DEFAULT NULL,
                INDEX IDX_DDCDD709CB39D93A (cell_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_cell (
                id INT AUTO_INCREMENT NOT NULL,
                question_id INT DEFAULT NULL,
                data LONGTEXT DEFAULT NULL,
                coordsX INT DEFAULT NULL,
                coordsY INT DEFAULT NULL,
                color VARCHAR(255) NOT NULL,
                background VARCHAR(255) NOT NULL,
                selector TINYINT(1) NOT NULL,
                input TINYINT(1) NOT NULL,
                uuid VARCHAR(36) NOT NULL,
                UNIQUE INDEX UNIQ_4ABE4F56D17F50A6 (uuid),
                INDEX IDX_4ABE4F561E27F6BF (question_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE ujm_question_grid
            ADD CONSTRAINT FK_2412DE371E27F6BF FOREIGN KEY (question_id)
            REFERENCES ujm_question (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_cell_choice
            ADD CONSTRAINT FK_DDCDD709CB39D93A FOREIGN KEY (cell_id)
            REFERENCES ujm_cell (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_cell
            ADD CONSTRAINT FK_4ABE4F561E27F6BF FOREIGN KEY (question_id)
            REFERENCES ujm_question_grid (id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_cell
            DROP FOREIGN KEY FK_4ABE4F561E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_cell_choice
            DROP FOREIGN KEY FK_DDCDD709CB39D93A
        ');
        $this->addSql('
            DROP TABLE ujm_question_grid
        ');
        $this->addSql('
            DROP TABLE ujm_cell_choice
        ');
        $this->addSql('
            DROP TABLE ujm_cell
        ');
    }
}
