<?php

namespace Icap\PortfolioBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/25 02:50:36
 */
class Version20140625145034 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__portfolio_users (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                portfolio_id INT NOT NULL, 
                INDEX IDX_3980F8F8A76ED395 (user_id), 
                INDEX IDX_3980F8F8B96B5643 (portfolio_id), 
                UNIQUE INDEX portfolio_users_unique_idx (portfolio_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                visibility INT NOT NULL, 
                disposition INT NOT NULL, 
                deletedAt DATETIME DEFAULT NULL, 
                INDEX IDX_8B1895DA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_groups (
                id INT AUTO_INCREMENT NOT NULL, 
                group_id INT NOT NULL, 
                portfolio_id INT NOT NULL, 
                INDEX IDX_9AF01ADFFE54D947 (group_id), 
                INDEX IDX_9AF01ADFB96B5643 (portfolio_id), 
                UNIQUE INDEX portfolio_groups_unique_idx (portfolio_id, group_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_abstract_widget (
                id INT AUTO_INCREMENT NOT NULL, 
                portfolio_id INT NOT NULL, 
                col INT DEFAULT 1 NOT NULL, 
                row INT DEFAULT 1 NOT NULL, 
                createdAt DATETIME NOT NULL, 
                updatedAt DATETIME NOT NULL, 
                widget_type VARCHAR(255) NOT NULL, 
                INDEX IDX_3E7AEFBBB96B5643 (portfolio_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_formations (
                id INT NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_formations_formation (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_id INT DEFAULT NULL, 
                widget_id INT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                INDEX IDX_D1BBD5B189329D25 (resource_id), 
                INDEX IDX_D1BBD5B1FBE885E2 (widget_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_title (
                id INT NOT NULL, 
                title VARCHAR(128) NOT NULL, 
                slug VARCHAR(128) NOT NULL, 
                UNIQUE INDEX UNIQ_1431A01D989D9B62 (slug), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_type (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                is_unique TINYINT(1) NOT NULL, 
                is_deletable TINYINT(1) NOT NULL, 
                UNIQUE INDEX UNIQ_3E00FC8F5E237E06 (name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_skills_skill (
                id INT AUTO_INCREMENT NOT NULL, 
                widget_id INT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                INDEX IDX_98EF40A3FBE885E2 (widget_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_user_information (
                id INT NOT NULL, 
                city VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_skills (
                id INT NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_presentation (
                id INT NOT NULL, 
                presentation LONGTEXT DEFAULT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_users 
            ADD CONSTRAINT FK_3980F8F8A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_users 
            ADD CONSTRAINT FK_3980F8F8B96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio 
            ADD CONSTRAINT FK_8B1895DA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_groups 
            ADD CONSTRAINT FK_9AF01ADFFE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_groups 
            ADD CONSTRAINT FK_9AF01ADFB96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD CONSTRAINT FK_3E7AEFBBB96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations 
            ADD CONSTRAINT FK_88739997BF396750 FOREIGN KEY (id) 
            REFERENCES icap__portfolio_abstract_widget (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations_formation 
            ADD CONSTRAINT FK_D1BBD5B189329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations_formation 
            ADD CONSTRAINT FK_D1BBD5B1FBE885E2 FOREIGN KEY (widget_id) 
            REFERENCES icap__portfolio_widget_formations (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_title 
            ADD CONSTRAINT FK_1431A01DBF396750 FOREIGN KEY (id) 
            REFERENCES icap__portfolio_abstract_widget (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_skills_skill 
            ADD CONSTRAINT FK_98EF40A3FBE885E2 FOREIGN KEY (widget_id) 
            REFERENCES icap__portfolio_widget_skills (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            ADD CONSTRAINT FK_E2BFAA03BF396750 FOREIGN KEY (id) 
            REFERENCES icap__portfolio_abstract_widget (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_skills 
            ADD CONSTRAINT FK_6C68C5A1BF396750 FOREIGN KEY (id) 
            REFERENCES icap__portfolio_abstract_widget (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_presentation 
            ADD CONSTRAINT FK_F0DBA727BF396750 FOREIGN KEY (id) 
            REFERENCES icap__portfolio_abstract_widget (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_users 
            DROP FOREIGN KEY FK_3980F8F8B96B5643
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_groups 
            DROP FOREIGN KEY FK_9AF01ADFB96B5643
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP FOREIGN KEY FK_3E7AEFBBB96B5643
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations 
            DROP FOREIGN KEY FK_88739997BF396750
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_title 
            DROP FOREIGN KEY FK_1431A01DBF396750
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            DROP FOREIGN KEY FK_E2BFAA03BF396750
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_skills 
            DROP FOREIGN KEY FK_6C68C5A1BF396750
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_presentation 
            DROP FOREIGN KEY FK_F0DBA727BF396750
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations_formation 
            DROP FOREIGN KEY FK_D1BBD5B1FBE885E2
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_skills_skill 
            DROP FOREIGN KEY FK_98EF40A3FBE885E2
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_users
        ");
        $this->addSql("
            DROP TABLE icap__portfolio
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_groups
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_abstract_widget
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_formations
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_formations_formation
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_title
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_type
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_skills_skill
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_user_information
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_skills
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_presentation
        ");
    }
}