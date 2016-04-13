<?php

namespace Icap\WikiBundle\Migrations\pdo_pgsql;

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
                id SERIAL NOT NULL, 
                user_id INT DEFAULT NULL, 
                section_id INT NOT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                text TEXT DEFAULT NULL, 
                creation_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                PRIMARY KEY(id)
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
            REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_contribution 
            ADD CONSTRAINT FK_781E6502D823E37A FOREIGN KEY (section_id) 
            REFERENCES icap__wiki_section (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            ADD active_contribution_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            DROP title
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            DROP text
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            DROP modification_date
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            ADD CONSTRAINT FK_82904AAFE665925 FOREIGN KEY (active_contribution_id) 
            REFERENCES icap__wiki_contribution (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_82904AAFE665925 ON icap__wiki_section (active_contribution_id)
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
            ADD title VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            ADD text TEXT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            ADD modification_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            DROP active_contribution_id
        ');
        $this->addSql('
            DROP INDEX UNIQ_82904AAFE665925
        ');
    }
}
