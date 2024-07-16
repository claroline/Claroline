<?php

namespace Claroline\CursusBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/07/16 11:56:48
 */
final class Version20240716115647 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD is_public TINYINT(1) NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            DROP is_public
        ');
    }
}
