<?php

namespace Claroline\OpenBadgeBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/08/28 04:31:51
 */
class Version20190828163150 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro__open_badge_rule (
                id INT AUTO_INCREMENT NOT NULL,
                badge_id INT DEFAULT NULL,
                node_id INT DEFAULT NULL,
                workspace_id INT DEFAULT NULL,
                role_id INT DEFAULT NULL,
                group_id INT DEFAULT NULL,
                action VARCHAR(255) NOT NULL,
                data LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)',
                uuid VARCHAR(36) NOT NULL,
                UNIQUE INDEX UNIQ_DE554AC7D17F50A6 (uuid),
                INDEX IDX_DE554AC7F7A2C2FC (badge_id),
                INDEX IDX_DE554AC7460D9FD7 (node_id),
                INDEX IDX_DE554AC782D40A1F (workspace_id),
                INDEX IDX_DE554AC7D60322AC (role_id),
                INDEX IDX_DE554AC7FE54D947 (group_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            ALTER TABLE claro__open_badge_rule
            ADD CONSTRAINT FK_DE554AC7F7A2C2FC FOREIGN KEY (badge_id)
            REFERENCES claro__open_badge_badge_class (id)
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_rule
            ADD CONSTRAINT FK_DE554AC7460D9FD7 FOREIGN KEY (node_id)
            REFERENCES claro_resource_node (id)
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_rule
            ADD CONSTRAINT FK_DE554AC782D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id)
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_rule
            ADD CONSTRAINT FK_DE554AC7D60322AC FOREIGN KEY (role_id)
            REFERENCES claro_role (id)
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_rule
            ADD CONSTRAINT FK_DE554AC7FE54D947 FOREIGN KEY (group_id)
            REFERENCES claro_group (id)
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_evidence
            ADD resourceEvidence_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_evidence
            ADD CONSTRAINT FK_6F6817330E7C438 FOREIGN KEY (resourceEvidence_id)
            REFERENCES claro_resource_user_evaluation (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_6F6817330E7C438 ON claro__open_badge_evidence (resourceEvidence_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro__open_badge_rule
        ');
    }
}
