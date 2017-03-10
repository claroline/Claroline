<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/03/01 04:48:50
 */
class Version20170301164849 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE ujm_question_ordering (
                id INT AUTO_INCREMENT NOT NULL,
                question_id INT DEFAULT NULL,
                direction VARCHAR(255) NOT NULL,
                mode VARCHAR(255) NOT NULL,
                penalty DOUBLE PRECISION NOT NULL,
                UNIQUE INDEX UNIQ_73DB988D1E27F6BF (question_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_ordering_item (
                id INT AUTO_INCREMENT NOT NULL,
                ujm_question_ordering_id INT DEFAULT NULL,
                position INT DEFAULT NULL,
                uuid VARCHAR(36) NOT NULL,
                data LONGTEXT DEFAULT NULL,
                score DOUBLE PRECISION NOT NULL,
                feedback LONGTEXT DEFAULT NULL,
                resourceNode_id INT DEFAULT NULL,
                UNIQUE INDEX UNIQ_360C6C62D17F50A6 (uuid),
                INDEX IDX_360C6C62273546DE (ujm_question_ordering_id),
                INDEX IDX_360C6C62B87FAB32 (resourceNode_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE ujm_question_ordering
            ADD CONSTRAINT FK_73DB988D1E27F6BF FOREIGN KEY (question_id)
            REFERENCES ujm_question (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_ordering_item
            ADD CONSTRAINT FK_360C6C62273546DE FOREIGN KEY (ujm_question_ordering_id)
            REFERENCES ujm_question_ordering (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_ordering_item
            ADD CONSTRAINT FK_360C6C62B87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_question_grid
            DROP FOREIGN KEY FK_2412DE371E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_question_grid
            ADD CONSTRAINT FK_2412DE371E27F6BF FOREIGN KEY (question_id)
            REFERENCES ujm_question (id)
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_ordering_item
            DROP FOREIGN KEY FK_360C6C62273546DE
        ');
        $this->addSql('
            DROP TABLE ujm_question_ordering
        ');
        $this->addSql('
            DROP TABLE ujm_ordering_item
        ');
        $this->addSql('
            ALTER TABLE ujm_question_grid
            DROP FOREIGN KEY FK_2412DE371E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_question_grid
            ADD CONSTRAINT FK_2412DE371E27F6BF FOREIGN KEY (question_id)
            REFERENCES ujm_question (id)
        ');
    }
}
