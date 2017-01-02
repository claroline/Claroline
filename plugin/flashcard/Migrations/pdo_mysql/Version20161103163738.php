<?php

namespace Claroline\FlashCardBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/11/03 04:37:41
 */
class Version20161103163738 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_fcbundle_user_preference
            ADD theme VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_deck
            ADD theme VARCHAR(255) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_fcbundle_deck
            DROP theme
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_user_preference
            DROP theme
        ');
    }
}
