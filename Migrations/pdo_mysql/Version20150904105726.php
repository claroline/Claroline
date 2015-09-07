<?php

namespace FormaLibre\PresenceBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/09/04 10:57:27
 */
class Version20150904105726 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_presence CHANGE Comment Comment VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_presence CHANGE Comment Comment VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ");
    }
}