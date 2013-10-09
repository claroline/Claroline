<?php

namespace Innova\PathBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/07 09:28:12
 */
class Version20131007092811 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_pathtemplate 
            DROP user, 
            DROP edit_date, 
            CHANGE description description LONGTEXT DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_pathtemplate 
            ADD user VARCHAR(255) NOT NULL, 
            ADD edit_date DATETIME NOT NULL, 
            CHANGE description description LONGTEXT NOT NULL
        ");
    }
}