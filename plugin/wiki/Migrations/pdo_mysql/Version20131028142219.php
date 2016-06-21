<?php

namespace Icap\WikiBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2013/10/28 02:22:20
 */
class Version20131028142219 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
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
            ALTER TABLE icap__wiki_section 
            ADD active_contribution_id INT DEFAULT NULL, 
            DROP title, 
            DROP text, 
            DROP modification_date
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            ADD CONSTRAINT FK_82904AAFE665925 FOREIGN KEY (active_contribution_id) 
            REFERENCES icap__wiki_contribution (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_82904AAFE665925 ON icap__wiki_section (active_contribution_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            DROP FOREIGN KEY FK_82904AAFE665925
        ');
        $this->addSql('
            DROP TABLE icap__wiki_contribution
        ');
        $this->addSql('
            DROP INDEX UNIQ_82904AAFE665925 ON icap__wiki_section
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            ADD title VARCHAR(255) DEFAULT NULL, 
            ADD text LONGTEXT DEFAULT NULL, 
            ADD modification_date DATETIME NOT NULL, 
            DROP active_contribution_id
        ');
    }
}
