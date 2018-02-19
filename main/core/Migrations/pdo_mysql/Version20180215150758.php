<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/02/15 03:07:59
 */
class Version20180215150758 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE user_organization DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE `user_organization` ADD `id` INT NOT NULL AUTO_INCREMENT AFTER `organization_id`, ADD PRIMARY KEY (`id`)');
        $this->addSql('
            ALTER TABLE claro_general_facet_preference CHANGE mail email TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro__organization
            ADD vat VARCHAR(255) DEFAULT NULL,
            ADD type VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE user_organization
            DROP FOREIGN KEY FK_41221F7E32C8A3DE
        ');
        $this->addSql('
            ALTER TABLE user_organization
            DROP FOREIGN KEY FK_41221F7EA76ED395
        ');
        $this->addSql('
            DROP INDEX IDX_41221F7E32C8A3DE ON user_organization
        ');
        $this->addSql('
            ALTER TABLE user_organization
            ADD is_main TINYINT(1) NOT NULL,
            CHANGE organization_id oganization_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE user_organization
            ADD CONSTRAINT FK_41221F7EF35E13B7 FOREIGN KEY (oganization_id)
            REFERENCES claro__organization (id)
        ');
        $this->addSql('
            ALTER TABLE user_organization
            ADD CONSTRAINT FK_41221F7EA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_41221F7EF35E13B7 ON user_organization (oganization_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__organization
            DROP vat,
            DROP type
        ');
        $this->addSql('
            ALTER TABLE claro_general_facet_preference CHANGE email mail TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE user_organization
            DROP FOREIGN KEY FK_41221F7EF35E13B7
        ');
        $this->addSql('
            ALTER TABLE user_organization
            DROP FOREIGN KEY FK_41221F7EA76ED395
        ');
        $this->addSql('
            DROP INDEX IDX_41221F7EF35E13B7 ON user_organization
        ');
        $this->addSql('
            ALTER TABLE user_organization
            DROP is_main,
            CHANGE oganization_id organization_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE user_organization
            ADD CONSTRAINT FK_41221F7E32C8A3DE FOREIGN KEY (organization_id)
            REFERENCES claro__organization (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE user_organization
            ADD CONSTRAINT FK_41221F7EA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_41221F7E32C8A3DE ON user_organization (organization_id)
        ');
    }
}
