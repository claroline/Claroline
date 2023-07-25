<?php

namespace Claroline\AuthenticationBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/25 12:06:59
 */
final class Version20230725120658 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_authentication_parameters 
            ADD helpMessage LONGTEXT DEFAULT NULL, 
            ADD changePassword TINYINT(1) NOT NULL, 
            ADD internalAccount TINYINT(1) NOT NULL, 
            ADD showClientIp TINYINT(1) NOT NULL, 
            ADD redirectAfterLoginOption VARCHAR(255) NOT NULL, 
            ADD redirectAfterLoginUrl VARCHAR(255) DEFAULT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_authentication_parameters 
            DROP helpMessage, 
            DROP changePassword, 
            DROP internalAccount, 
            DROP showClientIp, 
            DROP redirectAfterLoginOption, 
            DROP redirectAfterLoginUrl
        ');
    }
}
