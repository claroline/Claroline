<?php

namespace Claroline\PrivacyBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/06/20 10:07:16
 */
final class Version20230620100712 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_privacy_parameters 
            ADD publication_date DATETIME DEFAULT NULL, 
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_privacy_parameters 
            DROP publication_date, 
        ');
    }
}
