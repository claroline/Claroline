<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/25 10:19:38
 */
final class Version20230725101937 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_field_facet ADD confidentiality VARCHAR(255) NOT NULL
        ');

        $this->addSql('
            UPDATE claro_field_facet SET confidentiality = "none" WHERE is_metadata = 0
        ');

        $this->addSql('
            UPDATE claro_field_facet SET confidentiality = "owner" WHERE is_metadata = 1
        ');

        $this->addSql('
            ALTER TABLE claro_field_facet  
            DROP is_metadata
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_field_facet 
            ADD is_metadata TINYINT(1) DEFAULT 0 NOT NULL, 
            DROP confidentiality
        ');
    }
}
