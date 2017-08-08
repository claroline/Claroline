<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/08/08 02:30:44
 */
class Version20170808143043 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_cell_choice 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE ujm_cell_choice
            SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_DDCDD709D17F50A6 ON ujm_cell_choice (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_DDCDD709D17F50A6 ON ujm_cell_choice
        ');
        $this->addSql('
            ALTER TABLE ujm_cell_choice 
            DROP uuid
        ');
    }
}
