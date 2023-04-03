<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/04/03 06:40:09
 */
class Version20230403063954 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course CHANGE course_name entity_name VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session CHANGE course_name entity_name VARCHAR(255) NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course CHANGE entity_name course_name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session CHANGE entity_name course_name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`
        ');
    }
}
