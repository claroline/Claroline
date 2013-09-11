<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/11 09:36:38
 */
class Version20130911093634 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_user_badge (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                badge_id INT NOT NULL, 
                issuer_id INT DEFAULT NULL, 
                issued_at DATETIME NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_7EBB381FA76ED395 (user_id), 
                INDEX IDX_7EBB381FF7A2C2FC (badge_id), 
                INDEX IDX_7EBB381FBB9D6FEE (issuer_id), 
                UNIQUE INDEX user_badge_unique (user_id, badge_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_badge_claim (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                badge_id INT NOT NULL, 
                claimed_at DATETIME NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_487A496AA76ED395 (user_id), 
                INDEX IDX_487A496AF7A2C2FC (badge_id), 
                UNIQUE INDEX badge_claim_unique (user_id, badge_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_badge_translation (
                id INT AUTO_INCREMENT NOT NULL, 
                badge_id INT DEFAULT NULL, 
                locale VARCHAR(8) NOT NULL, 
                name VARCHAR(128) NOT NULL, 
                description VARCHAR(128) NOT NULL, 
                slug VARCHAR(128) NOT NULL, 
                criteria TEXT NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_849BC831F7A2C2FC (badge_id), 
                UNIQUE INDEX badge_translation_unique_idx (locale, badge_id), 
                UNIQUE INDEX badge_name_translation_unique_idx (name, locale, badge_id), 
                UNIQUE INDEX badge_slug_translation_unique_idx (slug, locale, badge_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_badge (
                id INT AUTO_INCREMENT NOT NULL, 
                version INT NOT NULL, 
                image VARCHAR(255) NOT NULL, 
                expired_at DATETIME DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge 
            ADD CONSTRAINT FK_7EBB381FA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge 
            ADD CONSTRAINT FK_7EBB381FF7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge 
            ADD CONSTRAINT FK_7EBB381FBB9D6FEE FOREIGN KEY (issuer_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge_claim 
            ADD CONSTRAINT FK_487A496AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_badge_claim 
            ADD CONSTRAINT FK_487A496AF7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_badge_translation 
            ADD CONSTRAINT FK_849BC831F7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user_badge 
            DROP FOREIGN KEY FK_7EBB381FF7A2C2FC
        ");
        $this->addSql("
            ALTER TABLE claro_badge_claim 
            DROP FOREIGN KEY FK_487A496AF7A2C2FC
        ");
        $this->addSql("
            ALTER TABLE claro_badge_translation 
            DROP FOREIGN KEY FK_849BC831F7A2C2FC
        ");
        $this->addSql("
            DROP TABLE claro_user_badge
        ");
        $this->addSql("
            DROP TABLE claro_badge_claim
        ");
        $this->addSql("
            DROP TABLE claro_badge_translation
        ");
        $this->addSql("
            DROP TABLE claro_badge
        ");
    }
}