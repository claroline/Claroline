<?php

namespace Icap\LessonBundle\Installation\Migrations\pdo_mysql;

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
            ALTER TABLE icap__lesson CHANGE show_overview show_overview TINYINT(1) DEFAULT "1" NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
