<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/01/27 03:20:19
 */
class Version20140127152017 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_content_translation (
                id INT IDENTITY NOT NULL, 
                locale NVARCHAR(8) NOT NULL, 
                object_class NVARCHAR(255) NOT NULL, 
                field NVARCHAR(32) NOT NULL, 
                foreign_key NVARCHAR(64) NOT NULL, 
                content VARCHAR(MAX), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX content_translation_idx ON claro_content_translation (
                locale, object_class, field, foreign_key
            )
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD hasAcceptedTerms BIT
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD is_enabled BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD is_mail_notified BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP CONSTRAINT FK_EB8D285282D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD CONSTRAINT FK_EB8D285282D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_icon 
            DROP COLUMN icon_location
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD associated_badge INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD userType SMALLINT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule ALTER COLUMN badge_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP CONSTRAINT FK_805FCB8FF7A2C2FC
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8F16F956BA FOREIGN KEY (associated_badge) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8FF7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_805FCB8F16F956BA ON claro_badge_rule (associated_badge)
        ");
        $this->addSql("
            ALTER TABLE claro_content 
            ADD type NVARCHAR(255)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_content_translation
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP COLUMN associated_badge
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP COLUMN userType
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule ALTER COLUMN badge_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP CONSTRAINT FK_805FCB8F16F956BA
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP CONSTRAINT FK_805FCB8FF7A2C2FC
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_805FCB8F16F956BA'
            ) 
            ALTER TABLE claro_badge_rule 
            DROP CONSTRAINT IDX_805FCB8F16F956BA ELSE 
            DROP INDEX IDX_805FCB8F16F956BA ON claro_badge_rule
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8FF7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_content 
            DROP COLUMN type
        ");
        $this->addSql("
            ALTER TABLE claro_resource_icon 
            ADD icon_location NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN hasAcceptedTerms
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN is_enabled
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN is_mail_notified
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP CONSTRAINT FK_EB8D285282D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD CONSTRAINT FK_EB8D285282D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ");
    }
}