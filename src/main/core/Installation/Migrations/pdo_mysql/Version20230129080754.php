<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/01/29 08:07:55
 */
class Version20230129080754 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE user_organization 
            DROP FOREIGN KEY FK_41221F7EF35E13B7
        ');
        $this->addSql('
            DROP INDEX IDX_41221F7EF35E13B7 ON user_organization
        ');
        $this->addSql('
            ALTER TABLE user_organization CHANGE oganization_id organization_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE user_organization 
            ADD CONSTRAINT FK_41221F7E32C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_41221F7E32C8A3DE ON user_organization (organization_id)
        ');

        $this->addSql('
            DELETE uo1
            FROM user_organization AS uo1
            INNER JOIN  
            (
                SELECT user_id, organization_id, is_main, min(id) as min_id
                FROM user_organization
                GROUP by user_id, organization_id
                HAVING count(*) > 1
            ) uo2 ON (uo1.user_id = uo2.user_id AND uo1.organization_id = uo2.organization_id)
            WHERE uo1.id > uo2.min_id
              AND (uo1.is_main = 0 OR uo2.is_main = 1)
        ');

        $this->addSql('
            CREATE UNIQUE INDEX organization_unique_user ON user_organization (user_id, organization_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE user_organization 
            DROP FOREIGN KEY FK_41221F7E32C8A3DE
        ');
        $this->addSql('
            DROP INDEX IDX_41221F7E32C8A3DE ON user_organization
        ');
        $this->addSql('
            DROP INDEX organization_unique_user ON user_organization
        ');
        $this->addSql('
            ALTER TABLE user_organization CHANGE organization_id oganization_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE user_organization 
            ADD CONSTRAINT FK_41221F7EF35E13B7 FOREIGN KEY (oganization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_41221F7EF35E13B7 ON user_organization (oganization_id)
        ');
    }
}
