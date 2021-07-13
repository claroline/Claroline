<?php

namespace Icap\WikiBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 09:13:26
 */
class Version20180620115033 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE icap__wiki_section (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                active_contribution_id INT DEFAULT NULL, 
                wiki_id INT DEFAULT NULL, 
                parent_id INT DEFAULT NULL, 
                visible TINYINT(1) NOT NULL, 
                creation_date DATETIME NOT NULL, 
                deleted TINYINT(1) DEFAULT NULL, 
                deletion_date DATETIME DEFAULT NULL, 
                lft INT NOT NULL, 
                lvl INT NOT NULL, 
                rgt INT NOT NULL, 
                root INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_82904AAD17F50A6 (uuid), 
                INDEX IDX_82904AAA76ED395 (user_id), 
                UNIQUE INDEX UNIQ_82904AAFE665925 (active_contribution_id), 
                INDEX IDX_82904AAAA948DBE (wiki_id), 
                INDEX IDX_82904AA727ACA70 (parent_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE icap__wiki_contribution (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                section_id INT DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                text LONGTEXT DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_781E6502D17F50A6 (uuid), 
                INDEX IDX_781E6502A76ED395 (user_id), 
                INDEX IDX_781E6502D823E37A (section_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE icap__wiki (
                id INT AUTO_INCREMENT NOT NULL, 
                root_id INT DEFAULT NULL, 
                mode SMALLINT DEFAULT NULL, 
                displaySectionNumbers TINYINT(1) NOT NULL, 
                display_contents TINYINT(1) DEFAULT '1' NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_1FAD6B81D17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_1FAD6B8179066886 (root_id), 
                UNIQUE INDEX UNIQ_1FAD6B81B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            ADD CONSTRAINT FK_82904AAA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            ADD CONSTRAINT FK_82904AAFE665925 FOREIGN KEY (active_contribution_id) 
            REFERENCES icap__wiki_contribution (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            ADD CONSTRAINT FK_82904AAAA948DBE FOREIGN KEY (wiki_id) 
            REFERENCES icap__wiki (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            ADD CONSTRAINT FK_82904AA727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES icap__wiki_section (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_contribution 
            ADD CONSTRAINT FK_781E6502A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_contribution 
            ADD CONSTRAINT FK_781E6502D823E37A FOREIGN KEY (section_id) 
            REFERENCES icap__wiki_section (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__wiki 
            ADD CONSTRAINT FK_1FAD6B8179066886 FOREIGN KEY (root_id) 
            REFERENCES icap__wiki_section (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__wiki 
            ADD CONSTRAINT FK_1FAD6B81B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            DROP FOREIGN KEY FK_82904AA727ACA70
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_contribution 
            DROP FOREIGN KEY FK_781E6502D823E37A
        ');
        $this->addSql('
            ALTER TABLE icap__wiki 
            DROP FOREIGN KEY FK_1FAD6B8179066886
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            DROP FOREIGN KEY FK_82904AAFE665925
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            DROP FOREIGN KEY FK_82904AAAA948DBE
        ');
        $this->addSql('
            DROP TABLE icap__wiki_section
        ');
        $this->addSql('
            DROP TABLE icap__wiki_contribution
        ');
        $this->addSql('
            DROP TABLE icap__wiki
        ');
    }
}
