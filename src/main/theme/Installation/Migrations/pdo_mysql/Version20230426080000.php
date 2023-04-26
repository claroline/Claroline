<?php

namespace Claroline\ThemeBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/04/21 08:37:49
 */
class Version20230426080000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_icon_item CHANGE svg svg TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_icon_set CHANGE is_default is_default TINYINT(1) DEFAULT "0" NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
