<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/08/28 03:05:15
 */
final class Version20240828150403 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_user 
            DROP code
        ');
        $this->addSql('
            ALTER TABLE claro__location 
            DROP type, 
            DROP latitude, 
            DROP longitude
        ');
        $this->addSql('
            DROP INDEX `primary` ON claro__location_organization
        ');
        $this->addSql('
            ALTER TABLE claro__location_organization 
            ADD PRIMARY KEY (location_id, organization_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro__location 
            ADD type INT NOT NULL, 
            ADD latitude DOUBLE PRECISION DEFAULT NULL, 
            ADD longitude DOUBLE PRECISION DEFAULT NULL
        ');
        $this->addSql('
            DROP INDEX `PRIMARY` ON claro__location_organization
        ');
        $this->addSql('
            ALTER TABLE claro__location_organization 
            ADD PRIMARY KEY (organization_id, location_id)
        ');
        $this->addSql('
            ALTER TABLE claro_user 
            ADD code VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`
        ');
    }
}
