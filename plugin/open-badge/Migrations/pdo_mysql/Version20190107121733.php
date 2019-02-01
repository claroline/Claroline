<?php

namespace Claroline\OpenBadgeBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/01/07 12:17:34
 */
class Version20190107121733 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro__open_badge_endorsement (
                id INT AUTO_INCREMENT NOT NULL,
                issuer_id INT DEFAULT NULL,
                verification_id INT DEFAULT NULL,
                claim LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)',
                issuedOn DATETIME NOT NULL,
                comment LONGTEXT NOT NULL,
                uuid VARCHAR(36) NOT NULL,
                UNIQUE INDEX UNIQ_F2235FAED17F50A6 (uuid),
                INDEX IDX_F2235FAEBB9D6FEE (issuer_id),
                INDEX IDX_F2235FAE1623CB0A (verification_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro__open_badge_verification_object (
                id INT AUTO_INCREMENT NOT NULL,
                verificationProperty VARCHAR(255) NOT NULL,
                startWith VARCHAR(255) NOT NULL,
                allowedOrigins LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)',
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro__open_badge_identity_object (
                id INT AUTO_INCREMENT NOT NULL,
                identity VARCHAR(255) NOT NULL,
                hashed TINYINT(1) NOT NULL,
                salt VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro__open_badge_signed_badge (
                id INT AUTO_INCREMENT NOT NULL,
                cryptographicKey_id INT DEFAULT NULL,
                INDEX IDX_F8B85F4EA7BA6769 (cryptographicKey_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro__open_badge_badge_class (
                id INT AUTO_INCREMENT NOT NULL,
                issuer_id INT DEFAULT NULL,
                workspace_id INT DEFAULT NULL,
                name VARCHAR(255) NOT NULL,
                description LONGTEXT NOT NULL,
                image VARCHAR(255) NOT NULL,
                criteria LONGTEXT NOT NULL,
                enabled TINYINT(1) DEFAULT NULL,
                durationValidation INT DEFAULT NULL,
                created DATETIME NOT NULL,
                updated DATETIME NOT NULL,
                issuingMode LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)',
                uuid VARCHAR(36) NOT NULL,
                UNIQUE INDEX UNIQ_7A1CAEBED17F50A6 (uuid),
                INDEX IDX_7A1CAEBEBB9D6FEE (issuer_id),
                INDEX IDX_7A1CAEBE82D40A1F (workspace_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE badgeclass_user (
                badgeclass_id INT NOT NULL,
                user_id INT NOT NULL,
                INDEX IDX_45DE1F8F98CCE3D1 (badgeclass_id),
                INDEX IDX_45DE1F8FA76ED395 (user_id),
                PRIMARY KEY(badgeclass_id, user_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE badgeclass_group (
                badgeclass_id INT NOT NULL,
                group_id INT NOT NULL,
                INDEX IDX_1F0F6E8998CCE3D1 (badgeclass_id),
                INDEX IDX_1F0F6E89FE54D947 (group_id),
                PRIMARY KEY(badgeclass_id, group_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro__open_badge_assertion (
                id INT AUTO_INCREMENT NOT NULL,
                recipient_id INT DEFAULT NULL,
                badge_id INT DEFAULT NULL,
                verification_id INT DEFAULT NULL,
                evidences_id INT DEFAULT NULL,
                issuedOn DATETIME NOT NULL,
                image LONGTEXT DEFAULT NULL,
                narrative LONGTEXT DEFAULT NULL,
                revoked TINYINT(1) NOT NULL,
                revocationReason LONGTEXT DEFAULT NULL,
                uuid VARCHAR(36) NOT NULL,
                UNIQUE INDEX UNIQ_B6E0ABADD17F50A6 (uuid),
                INDEX IDX_B6E0ABADE92F8F78 (recipient_id),
                INDEX IDX_B6E0ABADF7A2C2FC (badge_id),
                INDEX IDX_B6E0ABAD1623CB0A (verification_id),
                INDEX IDX_B6E0ABAD8D74B52B (evidences_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro__open_badge_revocation_list (
                id INT AUTO_INCREMENT NOT NULL,
                issuer_id INT DEFAULT NULL,
                INDEX IDX_4635F096BB9D6FEE (issuer_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE revocationlist_assertion (
                revocationlist_id INT NOT NULL,
                assertion_id INT NOT NULL,
                INDEX IDX_FDE09CC0412E672C (revocationlist_id),
                INDEX IDX_FDE09CC0245A6843 (assertion_id),
                PRIMARY KEY(revocationlist_id, assertion_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro__open_badge_evidence (
                id INT AUTO_INCREMENT NOT NULL,
                assertion_id INT DEFAULT NULL,
                narrative LONGTEXT NOT NULL,
                name VARCHAR(255) NOT NULL,
                description LONGTEXT DEFAULT NULL,
                genre VARCHAR(255) DEFAULT NULL,
                audience LONGTEXT DEFAULT NULL,
                uuid VARCHAR(36) NOT NULL,
                UNIQUE INDEX UNIQ_6F68173D17F50A6 (uuid),
                INDEX IDX_6F68173245A6843 (assertion_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE evidence_resourcenode (
                evidence_id INT NOT NULL,
                resourcenode_id INT NOT NULL,
                INDEX IDX_C594A170B528FC11 (evidence_id),
                INDEX IDX_C594A17077C292AE (resourcenode_id),
                PRIMARY KEY(evidence_id, resourcenode_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_endorsement
            ADD CONSTRAINT FK_F2235FAEBB9D6FEE FOREIGN KEY (issuer_id)
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_endorsement
            ADD CONSTRAINT FK_F2235FAE1623CB0A FOREIGN KEY (verification_id)
            REFERENCES claro__open_badge_verification_object (id)
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_signed_badge
            ADD CONSTRAINT FK_F8B85F4EA7BA6769 FOREIGN KEY (cryptographicKey_id)
            REFERENCES claro_cryptographic_key (id)
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_badge_class
            ADD CONSTRAINT FK_7A1CAEBEBB9D6FEE FOREIGN KEY (issuer_id)
            REFERENCES claro__organization (id)
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_badge_class
            ADD CONSTRAINT FK_7A1CAEBE82D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id)
        ');
        $this->addSql('
            ALTER TABLE badgeclass_user
            ADD CONSTRAINT FK_45DE1F8F98CCE3D1 FOREIGN KEY (badgeclass_id)
            REFERENCES claro__open_badge_badge_class (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE badgeclass_user
            ADD CONSTRAINT FK_45DE1F8FA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE badgeclass_group
            ADD CONSTRAINT FK_1F0F6E8998CCE3D1 FOREIGN KEY (badgeclass_id)
            REFERENCES claro__open_badge_badge_class (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE badgeclass_group
            ADD CONSTRAINT FK_1F0F6E89FE54D947 FOREIGN KEY (group_id)
            REFERENCES claro_group (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_assertion
            ADD CONSTRAINT FK_B6E0ABADE92F8F78 FOREIGN KEY (recipient_id)
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_assertion
            ADD CONSTRAINT FK_B6E0ABADF7A2C2FC FOREIGN KEY (badge_id)
            REFERENCES claro__open_badge_badge_class (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_assertion
            ADD CONSTRAINT FK_B6E0ABAD1623CB0A FOREIGN KEY (verification_id)
            REFERENCES claro__open_badge_verification_object (id)
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_assertion
            ADD CONSTRAINT FK_B6E0ABAD8D74B52B FOREIGN KEY (evidences_id)
            REFERENCES claro__open_badge_evidence (id)
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_revocation_list
            ADD CONSTRAINT FK_4635F096BB9D6FEE FOREIGN KEY (issuer_id)
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE revocationlist_assertion
            ADD CONSTRAINT FK_FDE09CC0412E672C FOREIGN KEY (revocationlist_id)
            REFERENCES claro__open_badge_revocation_list (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE revocationlist_assertion
            ADD CONSTRAINT FK_FDE09CC0245A6843 FOREIGN KEY (assertion_id)
            REFERENCES claro__open_badge_assertion (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_evidence
            ADD CONSTRAINT FK_6F68173245A6843 FOREIGN KEY (assertion_id)
            REFERENCES claro__open_badge_assertion (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE evidence_resourcenode
            ADD CONSTRAINT FK_C594A170B528FC11 FOREIGN KEY (evidence_id)
            REFERENCES claro__open_badge_evidence (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE evidence_resourcenode
            ADD CONSTRAINT FK_C594A17077C292AE FOREIGN KEY (resourcenode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_endorsement
            DROP FOREIGN KEY FK_F2235FAE1623CB0A
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_assertion
            DROP FOREIGN KEY FK_B6E0ABAD1623CB0A
        ');
        $this->addSql('
            ALTER TABLE badgeclass_user
            DROP FOREIGN KEY FK_45DE1F8F98CCE3D1
        ');
        $this->addSql('
            ALTER TABLE badgeclass_group
            DROP FOREIGN KEY FK_1F0F6E8998CCE3D1
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_assertion
            DROP FOREIGN KEY FK_B6E0ABADF7A2C2FC
        ');
        $this->addSql('
            ALTER TABLE revocationlist_assertion
            DROP FOREIGN KEY FK_FDE09CC0245A6843
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_evidence
            DROP FOREIGN KEY FK_6F68173245A6843
        ');
        $this->addSql('
            ALTER TABLE revocationlist_assertion
            DROP FOREIGN KEY FK_FDE09CC0412E672C
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_assertion
            DROP FOREIGN KEY FK_B6E0ABAD8D74B52B
        ');
        $this->addSql('
            ALTER TABLE evidence_resourcenode
            DROP FOREIGN KEY FK_C594A170B528FC11
        ');
        $this->addSql('
            DROP TABLE claro__open_badge_endorsement
        ');
        $this->addSql('
            DROP TABLE claro__open_badge_verification_object
        ');
        $this->addSql('
            DROP TABLE claro__open_badge_identity_object
        ');
        $this->addSql('
            DROP TABLE claro__open_badge_signed_badge
        ');
        $this->addSql('
            DROP TABLE claro__open_badge_badge_class
        ');
        $this->addSql('
            DROP TABLE badgeclass_user
        ');
        $this->addSql('
            DROP TABLE badgeclass_group
        ');
        $this->addSql('
            DROP TABLE claro__open_badge_assertion
        ');
        $this->addSql('
            DROP TABLE claro__open_badge_revocation_list
        ');
        $this->addSql('
            DROP TABLE revocationlist_assertion
        ');
        $this->addSql('
            DROP TABLE claro__open_badge_evidence
        ');
        $this->addSql('
            DROP TABLE evidence_resourcenode
        ');
    }
}
