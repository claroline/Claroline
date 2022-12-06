<?php

namespace Claroline\CommunityBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/12/09 09:42:33
 */
class Version20221209094218 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_team 
            DROP FOREIGN KEY FK_A2FE580455D548E
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            DROP FOREIGN KEY FK_A2FE580459E625D1
        ');
        $this->addSql('
            DROP INDEX IDX_A2FE580455D548E ON claro_team
        ');
        $this->addSql('
            DROP INDEX UNIQ_A2FE580459E625D1 ON claro_team
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            CHANGE team_manager_role manager_role_id INT DEFAULT NULL, 
            ADD thumbnail VARCHAR(255) DEFAULT NULL, 
            ADD poster VARCHAR(255) DEFAULT NULL, 
            DROP team_manager, 
            CHANGE name entity_name VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE580468CE17BA FOREIGN KEY (manager_role_id) 
            REFERENCES claro_role (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_A2FE580468CE17BA ON claro_team (manager_role_id)
        ');

        $this->addSql('
            UPDATE claro_role SET is_locked = 0 WHERE `name` = "ROLE_ADMIN_ORGANIZATION"
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_team 
            DROP FOREIGN KEY FK_A2FE580468CE17BA
        ');
        $this->addSql('
            DROP INDEX UNIQ_A2FE580468CE17BA ON claro_team
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            CHANGE manager_role_id team_manager_role INT DEFAULT NULL, 
            DROP thumbnail, 
            DROP poster, 
            ADD team_manager INT DEFAULT NULL, 
            CHANGE entity_name name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE580455D548E FOREIGN KEY (team_manager) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE580459E625D1 FOREIGN KEY (team_manager_role) 
            REFERENCES claro_role (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_A2FE580455D548E ON claro_team (team_manager)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_A2FE580459E625D1 ON claro_team (team_manager_role)
        ');
    }
}
