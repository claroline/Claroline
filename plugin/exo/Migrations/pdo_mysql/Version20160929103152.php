<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/09/29 10:31:55
 */
class Version20160929103152 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE ujm_question_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                `order` INT NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                INDEX IDX_B47B5FFCB87FAB32 (resourceNode_id), 
                INDEX IDX_B47B5FFC1E27F6BF (question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE ujm_question_resource 
            ADD CONSTRAINT FK_B47B5FFCB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_question_resource 
            ADD CONSTRAINT FK_B47B5FFC1E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_question 
            DROP locked
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE ujm_question_resource
        ');
        $this->addSql('
            ALTER TABLE ujm_question 
            ADD locked TINYINT(1) NOT NULL
        ');
    }
}
