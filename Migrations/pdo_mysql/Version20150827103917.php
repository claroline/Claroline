<?php

namespace FormaLibre\PresenceBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/08/27 10:39:21
 */
class Version20150827103917 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_status CHANGE statusName statusName VARCHAR(255) DEFAULT NULL, 
            CHANGE statusColor statusColor VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_status CHANGE statusName statusName VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
            CHANGE statusColor statusColor VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ");
    }
}