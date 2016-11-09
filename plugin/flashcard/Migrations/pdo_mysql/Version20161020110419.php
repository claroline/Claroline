<?php

namespace Claroline\FlashCardBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/10/20 11:04:36
 */
class Version20161020110419 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_fcbundle_card_learning CHANGE painfull painful TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_card_log CHANGE painfull painful TINYINT(1) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_fcbundle_card_learning CHANGE painful painfull TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_card_log CHANGE painful painfull TINYINT(1) NOT NULL
        ');
    }
}
