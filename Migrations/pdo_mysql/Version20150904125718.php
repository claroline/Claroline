<?php

namespace FormaLibre\PresenceBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/09/04 12:57:19
 */
class Version20150904125718 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_period 
            ADD visibility TINYINT(1) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_period 
            DROP visibility
        ");
    }
}