<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/04/18 11:40:23
 */
class Version20170418114021 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_field_facet_choice 
            ADD parent_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_choice 
            ADD CONSTRAINT FK_E2001D727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_field_facet_choice (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_E2001D727ACA70 ON claro_field_facet_choice (parent_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_field_facet_choice 
            DROP FOREIGN KEY FK_E2001D727ACA70
        ');
        $this->addSql('
            DROP INDEX IDX_E2001D727ACA70 ON claro_field_facet_choice
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_choice 
            DROP parent_id
        ');
    }
}
