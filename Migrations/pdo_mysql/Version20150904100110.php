<?php

namespace FormaLibre\PresenceBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/09/04 10:01:12
 */
class Version20150904100110 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_period 
            ADD school_date DATE NOT NULL, 
            CHANGE school_day school_day VARCHAR(255) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_period 
            DROP school_date, 
            CHANGE school_day school_day DATE NOT NULL
        ");
    }
}