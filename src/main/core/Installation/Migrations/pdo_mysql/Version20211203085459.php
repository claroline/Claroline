<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/12/03 08:55:08
 */
class Version20211203085459 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro__organization 
            ADD is_public TINYINT(1) NOT NULL
        ');

        $this->addSql('
            UPDATE claro__organization SET is_public = 0
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro__organization 
            DROP is_public
        ');
    }
}
