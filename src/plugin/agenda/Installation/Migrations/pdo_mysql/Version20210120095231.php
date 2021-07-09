<?php

namespace Claroline\AgendaBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/01/20 09:52:56
 */
class Version20210120095231 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_event 
            DROP is_editable
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_event 
            ADD is_editable TINYINT(1) DEFAULT NULL
        ');
    }
}
