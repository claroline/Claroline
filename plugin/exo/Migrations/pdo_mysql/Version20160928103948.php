<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/09/28 10:39:51
 */
class Version20160928103948 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_object_question 
            DROP PRIMARY KEY
        ');
        $this->addSql('
            ALTER TABLE ujm_object_question 
            ADD id INT AUTO_INCREMENT NOT NULL, 
            DROP description, 
            DROP `type`, 
            CHANGE question_id question_id INT DEFAULT NULL, 
            CHANGE resourceNode_id resourceNode_id INT DEFAULT NULL,
            ADD PRIMARY KEY (id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_object_question MODIFY id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_object_question 
            DROP PRIMARY KEY
        ');
        $this->addSql('
            ALTER TABLE ujm_object_question 
            ADD description LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, 
            ADD `type` LONGTEXT NOT NULL COLLATE utf8_unicode_ci, 
            DROP id, 
            CHANGE question_id question_id INT NOT NULL, 
            CHANGE resourceNode_id resourceNode_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_object_question 
            ADD PRIMARY KEY (resourceNode_id, question_id)
        ');
    }
}
