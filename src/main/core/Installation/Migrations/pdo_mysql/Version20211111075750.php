<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/11/11 07:58:04
 */
class Version20211111075750 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX UNIQ_EB8D2852181F3A64 ON claro_user
        ');
        $this->addSql('
            ALTER TABLE claro_user 
            DROP public_url, 
            DROP has_tuned_public_url
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_user 
            ADD public_url VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            ADD has_tuned_public_url TINYINT(1) NOT NULL
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_EB8D2852181F3A64 ON claro_user (public_url)
        ');
    }
}
