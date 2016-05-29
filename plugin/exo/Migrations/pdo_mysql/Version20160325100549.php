<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/03/25 10:05:51
 */
class Version20160325100549 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_paper 
            ADD anonymous TINYINT(1) DEFAULT NULL, 
            CHANGE score score DOUBLE PRECISION DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_paper 
            DROP anonymous, 
            CHANGE score score DOUBLE PRECISION NOT NULL
        ');
    }
}
