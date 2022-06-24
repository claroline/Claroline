<?php

namespace UJM\ExoBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/06/24 07:59:58
 */
class Version20220624075933 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE ujm_question_grid 
            CHANGE `rows` grid_rows INT NOT NULL, 
            CHANGE `columns` grid_columns INT NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE ujm_question_grid 
            CHANGE grid_rows `rows` INT NOT NULL, 
            CHANGE grid_columns `columns` INT NOT NULL
        ');
    }
}
