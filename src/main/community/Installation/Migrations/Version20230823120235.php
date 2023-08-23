<?php

namespace Claroline\CommunityBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/08/23 12:02:36
 */
final class Version20230823120235 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_team 
            ADD is_using_existing_roles TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            DROP FOREIGN KEY FK_A2FE5804D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            DROP INDEX UNIQ_A2FE5804D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            DROP FOREIGN KEY FK_A2FE580468CE17BA
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            DROP INDEX UNIQ_A2FE580468CE17BA
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_team 
            DROP is_using_existing_roles
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE5804D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            ADD UNIQUE INDEX UNIQ_A2FE5804D60322AC (role_id)
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE580468CE17BA FOREIGN KEY (manager_role_id) 
            REFERENCES claro_role (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            ADD UNIQUE INDEX UNIQ_A2FE580468CE17BA (manager_role_id)
        ');
    }
}
