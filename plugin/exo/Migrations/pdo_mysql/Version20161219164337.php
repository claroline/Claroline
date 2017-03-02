<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/12/19 04:43:40
 */
class Version20161219164337 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_interaction_open 
            ADD maxAnswerLength INT NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_interaction_open 
            DROP maxAnswerLength
        ');
    }
}
