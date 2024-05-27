<?php

namespace Claroline\HomeBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/04/11 10:41:07
 */
final class Version20240411104107 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_home_tab
            DROP centerTitle,
            DROP showTitle
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_home_tab
            ADD centerTitle TINYINT(1) NOT NULL,
            ADD showTitle TINYINT(1) DEFAULT 1 NOT NULL
        ');
    }
}
