<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/04/22 07:28:29
 */
class Version20200422072816 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_interaction_open 
            ADD contentType VARCHAR(255) NOT NULL
        ');

        $this->addSql('
            UPDATE ujm_interaction_open SET contentType = "text"
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_interaction_open 
            DROP contentType
        ');
    }
}
