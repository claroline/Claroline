<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/01/05 03:07:33
 */
class Version20170105150732 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_question 
            DROP FOREIGN KEY FK_2F606977A76ED395
        ');
        $this->addSql('
            ALTER TABLE ujm_question 
            ADD CONSTRAINT FK_2F606977A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_exercise 
            ADD show_feedback TINYINT(1) NOT NULL DEFAULT 0
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            DROP show_feedback
        ');
        $this->addSql('
            ALTER TABLE ujm_question 
            DROP FOREIGN KEY FK_2F606977A76ED395
        ');
        $this->addSql('
            ALTER TABLE ujm_question 
            ADD CONSTRAINT FK_2F606977A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
    }
}
