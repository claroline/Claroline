<?php

namespace Claroline\AgendaBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/04/21 06:10:34
 */
class Version20210421061027 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_event_invitation 
            DROP title, 
            DROP description, 
            CHANGE status status VARCHAR(255) NOT NULL
        ');

        $this->addSql('
            UPDATE claro_event_invitation SET `status` = "unknown" WHERE `status` = "0"
        ');

        $this->addSql('
            UPDATE claro_event_invitation SET `status` = "join" WHERE `status` = "1"
        ');

        $this->addSql('
            UPDATE claro_event_invitation SET `status` = "maybe" WHERE `status` = "2"
        ');

        $this->addSql('
            UPDATE claro_event_invitation SET `status` = "resign" WHERE `status` = "3"
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_event_invitation 
            ADD title VARCHAR(50) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            ADD description LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            CHANGE status status SMALLINT NOT NULL
        ');
    }
}
