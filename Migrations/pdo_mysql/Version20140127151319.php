<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/01/27 03:13:20
 */
class Version20140127151319 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_content_translation (
                id INT AUTO_INCREMENT NOT NULL, 
                locale VARCHAR(8) NOT NULL, 
                object_class VARCHAR(255) NOT NULL, 
                field VARCHAR(32) NOT NULL, 
                foreign_key VARCHAR(64) NOT NULL, 
                content LONGTEXT DEFAULT NULL, 
                INDEX content_translation_idx (
                    locale, object_class, field, foreign_key
                ), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP FOREIGN KEY FK_EB8D285282D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD hasAcceptedTerms TINYINT(1) DEFAULT NULL, 
            ADD is_enabled TINYINT(1) NOT NULL, 
            ADD is_mail_notified TINYINT(1) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD CONSTRAINT FK_EB8D285282D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_icon 
            DROP icon_location
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP FOREIGN KEY FK_805FCB8FF7A2C2FC
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD associated_badge INT NOT NULL, 
            ADD userType SMALLINT NOT NULL, 
            CHANGE badge_id badge_id INT DEFAULT NULL
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
            DROP FOREIGN KEY FK_805FCB8F16F956BA
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP FOREIGN KEY FK_805FCB8FF7A2C2FC
        ");
        $this->addSql("
            DROP INDEX IDX_805FCB8F16F956BA ON claro_badge_rule
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP associated_badge, 
            DROP userType, 
            CHANGE badge_id badge_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8FF7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE
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
            DROP FOREIGN KEY FK_EB8D285282D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP hasAcceptedTerms, 
            DROP is_enabled, 
            DROP is_mail_notified
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD CONSTRAINT FK_EB8D285282D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ");
    }
}