<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/05/14 07:54:24
 */
class Version20210514075415 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro__organization CHANGE code code VARCHAR(255) NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro__organization CHANGE code code VARCHAR(36) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`
        ');
    }
}
