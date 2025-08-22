<?php

namespace Claroline\EvaluationBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/12/18 08:30:19
 */
final class Version20241218083015 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE innova_path 
            DROP FOREIGN KEY FK_CE19F054B87FAB32
        ');
        $this->addSql('
            DROP INDEX UNIQ_CE19F054B87FAB32 ON innova_path
        ');
        $this->addSql('
            ALTER TABLE innova_path 
            ADD entity_name VARCHAR(255) NOT NULL,
            ADD code VARCHAR(255) NOT NULL, 
            ADD thumbnail VARCHAR(255) DEFAULT NULL, 
            ADD poster VARCHAR(255) DEFAULT NULL, 
            ADD description_html LONGTEXT DEFAULT NULL, 
            ADD createdAt DATETIME DEFAULT NULL, 
            ADD updatedAt DATETIME DEFAULT NULL, 
            ADD published TINYINT(1) DEFAULT 1 NOT NULL, 
            ADD evaluated TINYINT(1) DEFAULT 0 NOT NULL, 
            ADD required TINYINT(1) DEFAULT 0 NOT NULL, 
            ADD creator_id INT DEFAULT NULL, 
            ADD workspace_id INT DEFAULT NULL,
            ADD estimatedDuration INT DEFAULT NULL,
            ADD accessible_from DATETIME DEFAULT NULL, 
            ADD accessible_until DATETIME DEFAULT NULL, 
            DROP show_overview
        ');

        $this->addSql('
            UPDATE innova_path SET description_html = description
        ');

        $this->addSql('
            UPDATE innova_path AS p 
            LEFT JOIN claro_resource_node AS n ON p.resourceNode_id = n.id
            SET 
                p.uuid = n.uuid,
                p.entity_name = n.name,
                p.description = n.description,
                p.code = n.code,
                p.poster = n.poster,
                p.createdAt = n.creation_date,
                p.updatedAt = n.modification_date,
                p.published = n.published,
                p.evaluated = n.evaluated,
                p.required = n.required,
                p.creator_id = n.creator_id,
                p.workspace_id = n.workspace_id,
                p.estimatedDuration = n.estimatedDuration,
                p.accessible_from = n.accessible_from,
                p.accessible_until = n.accessible_until
            WHERE p.resourceNode_id IS NOT NULL
              AND n.id IS NOT NULL
              AND n.active = 1
        ');

        $this->addSql('
            UPDATE innova_path SET code = uuid WHERE code IS NULL OR code = "" 
        ');

        $this->addSql('
            ALTER TABLE innova_path 
            ADD CONSTRAINT FK_CE19F05461220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE innova_path 
            ADD CONSTRAINT FK_CE19F05482D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_CE19F05477153098 ON innova_path (code)
        ');
        $this->addSql('
            CREATE INDEX IDX_CE19F05461220EA6 ON innova_path (creator_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_CE19F05482D40A1F ON innova_path (workspace_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE innova_path 
            DROP FOREIGN KEY FK_CE19F05461220EA6
        ');
        $this->addSql('
            ALTER TABLE innova_path 
            DROP FOREIGN KEY FK_CE19F05482D40A1F
        ');
        $this->addSql('
            DROP INDEX UNIQ_CE19F05477153098 ON innova_path
        ');
        $this->addSql('
            DROP INDEX IDX_CE19F05461220EA6 ON innova_path
        ');
        $this->addSql('
            DROP INDEX IDX_CE19F05482D40A1F ON innova_path
        ');
        $this->addSql('
            ALTER TABLE innova_path 
            ADD show_overview TINYINT(1) NOT NULL, 
            ADD resourceNode_id INT DEFAULT NULL, 
            DROP code, 
            DROP thumbnail, 
            DROP poster, 
            DROP description_html, 
            DROP createdAt, 
            DROP updatedAt, 
            DROP published, 
            DROP evaluated, 
            DROP required, 
            DROP estimatedDuration, 
            DROP creator_id, 
            DROP workspace_id
        ');
        $this->addSql('
            ALTER TABLE innova_path 
            ADD CONSTRAINT FK_CE19F054B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) ON UPDATE NO ACTION 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_CE19F054B87FAB32 ON innova_path (resourceNode_id)
        ');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
