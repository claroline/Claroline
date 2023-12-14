<?php

namespace Claroline\AuthenticationBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/11/05 06:13:33
 */
final class Version20231105061318 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_authentication_parameters 
            DROP redirectAfterLoginOption, 
            DROP redirectAfterLoginUrl
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_authentication_parameters 
            ADD redirectAfterLoginOption VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, 
            ADD redirectAfterLoginUrl VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`
        ');
    }
}
