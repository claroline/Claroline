<?php

namespace Icap\BadgeBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/05/06 09:11:17
 */
class Version20150506091116 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_badge_collection (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                slug VARCHAR(128) NOT NULL, 
                is_shared TINYINT(1) NOT NULL, 
                INDEX IDX_BB3FD2DDA76ED395 (user_id), 
                UNIQUE INDEX slug_idx (slug), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_badge_collection_user_badges (
                badgecollection_id INT NOT NULL, 
                userbadge_id INT NOT NULL, 
                INDEX IDX_85F018D4134B8A11 (badgecollection_id), 
                INDEX IDX_85F018D4DBE73D8B (userbadge_id), 
                PRIMARY KEY(
                    badgecollection_id, userbadge_id
                )
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_badge_translation (
                id INT AUTO_INCREMENT NOT NULL, 
                badge_id INT DEFAULT NULL, 
                locale VARCHAR(8) NOT NULL, 
                name VARCHAR(128) NOT NULL, 
                description VARCHAR(128) NOT NULL, 
                slug VARCHAR(128) NOT NULL, 
                criteria LONGTEXT NOT NULL, 
                INDEX IDX_849BC831F7A2C2FC (badge_id), 
                UNIQUE INDEX badge_translation_unique_idx (locale, badge_id), 
                UNIQUE INDEX badge_name_translation_unique_idx (name, locale, badge_id), 
                UNIQUE INDEX badge_slug_translation_unique_idx (slug, locale, badge_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_badge (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                version SMALLINT NOT NULL, 
                automatic_award TINYINT(1) DEFAULT NULL, 
                image VARCHAR(255) NOT NULL, 
                is_expiring TINYINT(1) DEFAULT '0' NOT NULL, 
                expire_duration INT DEFAULT NULL, 
                expire_period SMALLINT DEFAULT NULL, 
                deletedAt DATETIME DEFAULT NULL, 
                INDEX IDX_74F39F0F82D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_badge_claim (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                badge_id INT NOT NULL, 
                claimed_at DATETIME NOT NULL, 
                INDEX IDX_487A496AA76ED395 (user_id), 
                INDEX IDX_487A496AF7A2C2FC (badge_id), 
                UNIQUE INDEX badge_claim_unique (user_id, badge_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_user_badge (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                badge_id INT NOT NULL, 
                issuer_id INT DEFAULT NULL, 
                issued_at DATETIME NOT NULL, 
                expired_at DATETIME DEFAULT NULL, 
                comment LONGTEXT DEFAULT NULL, 
                INDEX IDX_7EBB381FA76ED395 (user_id), 
                INDEX IDX_7EBB381FF7A2C2FC (badge_id), 
                INDEX IDX_7EBB381FBB9D6FEE (issuer_id), 
                UNIQUE INDEX user_badge_unique (user_id, badge_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_badge_rule (
                id INT AUTO_INCREMENT NOT NULL, 
                badge_id INT DEFAULT NULL, 
                associated_badge INT NOT NULL, 
                resource_id INT DEFAULT NULL, 
                occurrence SMALLINT NOT NULL, 
                action VARCHAR(255) NOT NULL, 
                result VARCHAR(255) DEFAULT NULL, 
                resultMax VARCHAR(255) DEFAULT NULL, 
                resultComparison SMALLINT DEFAULT NULL, 
                userType SMALLINT NOT NULL, 
                active_from DATETIME DEFAULT NULL, 
                active_until DATETIME DEFAULT NULL, 
                INDEX IDX_805FCB8FF7A2C2FC (badge_id), 
                INDEX IDX_805FCB8F16F956BA (associated_badge), 
                INDEX IDX_805FCB8F89329D25 (resource_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_widget_badge_usage_config (
                id INT AUTO_INCREMENT NOT NULL, 
                numberLastAwardedBadge SMALLINT NOT NULL, 
                numberMostAwardedBadge SMALLINT NOT NULL, 
                widgetInstance_id INT DEFAULT NULL, 
                INDEX IDX_9A2EA78BAB7B5A55 (widgetInstance_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_badge_collection 
            ADD CONSTRAINT FK_BB3FD2DDA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE claro_badge_collection_user_badges 
            ADD CONSTRAINT FK_85F018D4134B8A11 FOREIGN KEY (badgecollection_id) 
            REFERENCES claro_badge_collection (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_badge_collection_user_badges 
            ADD CONSTRAINT FK_85F018D4DBE73D8B FOREIGN KEY (userbadge_id) 
            REFERENCES claro_user_badge (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_badge_translation 
            ADD CONSTRAINT FK_849BC831F7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_badge 
            ADD CONSTRAINT FK_74F39F0F82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_badge_claim 
            ADD CONSTRAINT FK_487A496AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_badge_claim 
            ADD CONSTRAINT FK_487A496AF7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_user_badge 
            ADD CONSTRAINT FK_7EBB381FA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_user_badge 
            ADD CONSTRAINT FK_7EBB381FF7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_user_badge 
            ADD CONSTRAINT FK_7EBB381FBB9D6FEE FOREIGN KEY (issuer_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8FF7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id)
        ');
        $this->addSql('
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8F16F956BA FOREIGN KEY (associated_badge) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8F89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_badge_usage_config 
            ADD CONSTRAINT FK_9A2EA78BAB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_badge_collection_user_badges 
            DROP FOREIGN KEY FK_85F018D4134B8A11
        ');
        $this->addSql('
            ALTER TABLE claro_badge_translation 
            DROP FOREIGN KEY FK_849BC831F7A2C2FC
        ');
        $this->addSql('
            ALTER TABLE claro_badge_claim 
            DROP FOREIGN KEY FK_487A496AF7A2C2FC
        ');
        $this->addSql('
            ALTER TABLE claro_user_badge 
            DROP FOREIGN KEY FK_7EBB381FF7A2C2FC
        ');
        $this->addSql('
            ALTER TABLE claro_badge_rule 
            DROP FOREIGN KEY FK_805FCB8FF7A2C2FC
        ');
        $this->addSql('
            ALTER TABLE claro_badge_rule 
            DROP FOREIGN KEY FK_805FCB8F16F956BA
        ');
        $this->addSql('
            ALTER TABLE claro_badge_collection_user_badges 
            DROP FOREIGN KEY FK_85F018D4DBE73D8B
        ');
        $this->addSql('
            DROP TABLE claro_badge_collection
        ');
        $this->addSql('
            DROP TABLE claro_badge_collection_user_badges
        ');
        $this->addSql('
            DROP TABLE claro_badge_translation
        ');
        $this->addSql('
            DROP TABLE claro_badge
        ');
        $this->addSql('
            DROP TABLE claro_badge_claim
        ');
        $this->addSql('
            DROP TABLE claro_user_badge
        ');
        $this->addSql('
            DROP TABLE claro_badge_rule
        ');
        $this->addSql('
            DROP TABLE claro_widget_badge_usage_config
        ');
    }
}
