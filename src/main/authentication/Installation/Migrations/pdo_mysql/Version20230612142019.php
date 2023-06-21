<?php

namespace Claroline\AuthenticationBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/06/12 02:20:20
 */
final class Version20230612142019 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_authentication_parameters (
                id INT AUTO_INCREMENT NOT NULL, 
                minLength INT NOT NULL, 
                requireLowercase TINYINT(1) NOT NULL, 
                requireUppercase TINYINT(1) NOT NULL, 
                requireSpecialChar TINYINT(1) NOT NULL, 
                requireNumber TINYINT(1) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_authentication_parameters
        ');
    }
}
