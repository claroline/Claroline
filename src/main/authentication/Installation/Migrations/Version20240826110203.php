<?php

namespace Claroline\AuthenticationBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/08/26 11:03:14
 */
final class Version20240826110203 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_ip_user 
            ADD uuid VARCHAR(36) NOT NULL
        ');

        $this->addSql('
            UPDATE claro_ip_user SET uuid = (SELECT UUID())
        ');

        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_FEB73761D17F50A6 ON claro_ip_user (uuid)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX UNIQ_FEB73761D17F50A6 ON claro_ip_user
        ');
        $this->addSql('
            ALTER TABLE claro_ip_user 
            DROP uuid
        ');
    }
}
