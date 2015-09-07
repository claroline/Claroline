<?php

namespace FormaLibre\PresenceBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/09/03 09:00:50
 */
class Version20150903210047 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_presence 
            ADD Comment VARCHAR(255) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_presence 
            DROP Comment
        ");
    }
}