<?php

namespace UJM\ExoBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/10/16 08:20:01
 */
class Version20201016081949 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            ADD questionNumbering VARCHAR(255) NOT NULL, 
            ADD showQuestionTitles TINYINT(1) NOT NULL
        ');

        $this->addSql('
            UPDATE ujm_exercise SET questionNumbering = numbering, showQuestionTitles = showTitles 
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            DROP questionNumbering, 
            DROP showQuestionTitles
        ');
    }
}
