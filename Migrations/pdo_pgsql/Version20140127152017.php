<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/01/27 03:20:18
 */
class Version20140127152017 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_content_translation (
                id SERIAL NOT NULL, 
                locale VARCHAR(8) NOT NULL, 
                object_class VARCHAR(255) NOT NULL, 
                field VARCHAR(32) NOT NULL, 
                foreign_key VARCHAR(64) NOT NULL, 
                content TEXT DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX content_translation_idx ON claro_content_translation (
                locale, object_class, field, foreign_key
            )
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP CONSTRAINT FK_EB8D285282D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD hasAcceptedTerms BOOLEAN DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD is_enabled BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD is_mail_notified BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD CONSTRAINT FK_EB8D285282D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_icon 
            DROP icon_location
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP CONSTRAINT FK_805FCB8FF7A2C2FC
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
            ALTER TABLE claro_badge_rule ALTER badge_id 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8F16F956BA FOREIGN KEY (associated_badge) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8FF7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            CREATE INDEX IDX_805FCB8F16F956BA ON claro_badge_rule (associated_badge)
        ");
        $this->addSql("
            ALTER TABLE claro_content 
            ADD type VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_content_translation
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
            DROP INDEX IDX_805FCB8F16F956BA
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP associated_badge
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP userType
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule ALTER badge_id 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8FF7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_content 
            DROP type
        ");
        $this->addSql("
            ALTER TABLE claro_resource_icon 
            ADD icon_location VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP CONSTRAINT FK_EB8D285282D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP hasAcceptedTerms
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP is_enabled
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP is_mail_notified
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD CONSTRAINT FK_EB8D285282D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
    }
}