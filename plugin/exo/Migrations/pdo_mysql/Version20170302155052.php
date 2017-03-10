<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/03/02 03:50:53
 */
class Version20170302155052 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_object_question
            DROP FOREIGN KEY FK_F91814BFB87FAB32
        ');
        $this->addSql('
            DROP INDEX IDX_F91814BFB87FAB32 ON ujm_object_question
        ');
        $this->addSql('
            ALTER TABLE ujm_object_question 
            ADD uuid VARCHAR(36) NOT NULL, 
            ADD mime_type VARCHAR(255) NOT NULL, 
            ADD object_data LONGTEXT NOT NULL, 
            DROP resourceNode_id
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_F91814BFD17F50A6 ON ujm_object_question (uuid)
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
            DROP INDEX UNIQ_F91814BFD17F50A6 ON ujm_object_question
        ');
        $this->addSql('
            ALTER TABLE ujm_object_question 
            ADD resourceNode_id INT DEFAULT NULL, 
            DROP uuid, 
            DROP mime_type, 
            DROP object_data
        ');
        $this->addSql('
            ALTER TABLE ujm_object_question 
            ADD CONSTRAINT FK_F91814BFB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_F91814BFB87FAB32 ON ujm_object_question (resourceNode_id)
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
