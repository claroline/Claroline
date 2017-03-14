<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/03/14 08:55:27
 */
class Version20170314085526 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE ujm_boolean_question (
                id INT AUTO_INCREMENT NOT NULL,
                question_id INT DEFAULT NULL,
                UNIQUE INDEX UNIQ_131D51461E27F6BF (question_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_boolean_choice (
                id INT AUTO_INCREMENT NOT NULL,
                boolean_question_id INT DEFAULT NULL,
                uuid VARCHAR(36) NOT NULL,
                score DOUBLE PRECISION NOT NULL,
                feedback LONGTEXT DEFAULT NULL,
                data LONGTEXT DEFAULT NULL,
                resourceNode_id INT DEFAULT NULL,
                UNIQUE INDEX UNIQ_B9216398D17F50A6 (uuid),
                INDEX IDX_B9216398B87FAB32 (resourceNode_id),
                INDEX IDX_B921639850B1C2F9 (boolean_question_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE ujm_boolean_question
            ADD CONSTRAINT FK_131D51461E27F6BF FOREIGN KEY (question_id)
            REFERENCES ujm_question (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_boolean_choice
            ADD CONSTRAINT FK_B9216398B87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_boolean_choice
            ADD CONSTRAINT FK_B921639850B1C2F9 FOREIGN KEY (boolean_question_id)
            REFERENCES ujm_boolean_question (id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_boolean_choice
            DROP FOREIGN KEY FK_B921639850B1C2F9
        ');
        $this->addSql('
            DROP TABLE ujm_boolean_question
        ');
        $this->addSql('
            DROP TABLE ujm_boolean_choice
        ');
    }
}
