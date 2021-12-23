<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/12/21 10:37:49
 */
class Version20211221103721 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            ADD registration_mail TINYINT(1) NOT NULL
        ');

        $this->addSql('
            UPDATE claro_cursusbundle_session_event SET registration_mail = 1 
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            DROP registration_mail
        ');
    }
}
