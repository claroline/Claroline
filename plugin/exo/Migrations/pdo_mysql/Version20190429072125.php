<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/04/29 07:21:55
 */
class Version20190429072125 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            ADD expected_answers TINYINT(1) NOT NULL
        ');

        $this->addSql('
            UPDATE ujm_exercise SET expected_answers = true WHERE `type` != "survey"
        ');

        $this->addSql('
            UPDATE ujm_exercise SET expected_answers = false WHERE `type` = "survey"
        ');

        $this->addSql('
            ALTER TABLE ujm_question 
            ADD expected_answers TINYINT(1) NOT NULL
        ');

        $this->addSql('
            UPDATE ujm_question SET expected_answers = true
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            DROP expected_answers
        ');

        $this->addSql('
            ALTER TABLE ujm_question 
            DROP expected_answers
        ');
    }
}
