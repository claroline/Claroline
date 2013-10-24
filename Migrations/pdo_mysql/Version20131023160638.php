<?php

namespace Icap\WikiBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/23 04:06:39
 */
class Version20131023160638 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__wiki_section (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                active_contribution_id INT DEFAULT NULL, 
                wiki_id INT NOT NULL, 
                parent_id INT DEFAULT NULL, 
                visible TINYINT(1) NOT NULL, 
                creation_date DATETIME NOT NULL, 
                lft INT NOT NULL, 
                lvl INT NOT NULL, 
                rgt INT NOT NULL, 
                root INT DEFAULT NULL, 
                INDEX IDX_82904AAA76ED395 (user_id), 
                UNIQUE INDEX UNIQ_82904AAFE665925 (active_contribution_id), 
                INDEX IDX_82904AAAA948DBE (wiki_id), 
                INDEX IDX_82904AA727ACA70 (parent_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE icap__wiki_contribution (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                section_id INT NOT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                text LONGTEXT DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                INDEX IDX_781E6502A76ED395 (user_id), 
                INDEX IDX_781E6502D823E37A (section_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE icap__wiki (
                id INT AUTO_INCREMENT NOT NULL, 
                root_id INT DEFAULT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_1FAD6B8179066886 (root_id), 
                UNIQUE INDEX UNIQ_1FAD6B81B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            ADD CONSTRAINT FK_82904AAA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            ADD CONSTRAINT FK_82904AAFE665925 FOREIGN KEY (active_contribution_id) 
            REFERENCES icap__wiki_contribution (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            ADD CONSTRAINT FK_82904AAAA948DBE FOREIGN KEY (wiki_id) 
            REFERENCES icap__wiki (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            ADD CONSTRAINT FK_82904AA727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES icap__wiki_section (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_contribution 
            ADD CONSTRAINT FK_781E6502A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_contribution 
            ADD CONSTRAINT FK_781E6502D823E37A FOREIGN KEY (section_id) 
            REFERENCES icap__wiki_section (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__wiki 
            ADD CONSTRAINT FK_1FAD6B8179066886 FOREIGN KEY (root_id) 
            REFERENCES icap__wiki_section (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__wiki 
            ADD CONSTRAINT FK_1FAD6B81B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            DROP FOREIGN KEY FK_82904AA727ACA70
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_contribution 
            DROP FOREIGN KEY FK_781E6502D823E37A
        ");
        $this->addSql("
            ALTER TABLE icap__wiki 
            DROP FOREIGN KEY FK_1FAD6B8179066886
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            DROP FOREIGN KEY FK_82904AAFE665925
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            DROP FOREIGN KEY FK_82904AAAA948DBE
        ");
        $this->addSql("
            DROP TABLE icap__wiki_section
        ");
        $this->addSql("
            DROP TABLE icap__wiki_contribution
        ");
        $this->addSql("
            DROP TABLE icap__wiki
        ");
    }
}