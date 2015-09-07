<?php

namespace FormaLibre\PresenceBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/09/03 01:12:57
 */
class Version20150903131252 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_period CHANGE school_day school_day TIME NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_period CHANGE school_day school_day VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ");
    }
}