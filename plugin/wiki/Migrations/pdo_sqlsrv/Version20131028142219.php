<?php

namespace Icap\WikiBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2013/10/28 02:22:21
 */
class Version20131028142219 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE icap__wiki_contribution (
                id INT IDENTITY NOT NULL, 
                user_id INT, 
                section_id INT NOT NULL, 
                title NVARCHAR(255), 
                text VARCHAR(MAX), 
                creation_date DATETIME2(6) NOT NULL, 
                PRIMARY KEY (id)
            )
        ');
        $this->addSql('
            CREATE INDEX IDX_781E6502A76ED395 ON icap__wiki_contribution (user_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_781E6502D823E37A ON icap__wiki_contribution (section_id)
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
            ADD active_contribution_id INT
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            DROP COLUMN title
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            DROP COLUMN text
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            DROP COLUMN modification_date
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            ADD CONSTRAINT FK_82904AAFE665925 FOREIGN KEY (active_contribution_id) 
            REFERENCES icap__wiki_contribution (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_82904AAFE665925 ON icap__wiki_section (active_contribution_id) 
            WHERE active_contribution_id IS NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            DROP CONSTRAINT FK_82904AAFE665925
        ');
        $this->addSql('
            DROP TABLE icap__wiki_contribution
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            ADD title NVARCHAR(255)
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            ADD text VARCHAR(MAX)
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            ADD modification_date DATETIME2(6) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            DROP COLUMN active_contribution_id
        ');
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_82904AAFE665925'
            ) 
            ALTER TABLE icap__wiki_section 
            DROP CONSTRAINT UNIQ_82904AAFE665925 ELSE 
            DROP INDEX UNIQ_82904AAFE665925 ON icap__wiki_section
        ");
    }
}
