<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/10/02 09:27:03
 */
class Version20211002092659 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_field_facet CHANGE type type VARCHAR(255) NOT NULL
        ');

        $this->addSql('
            UPDATE claro_field_facet SET type = "string" WHERE type = "1"
        ');
        $this->addSql('
            UPDATE claro_field_facet SET type = "number" WHERE type = "2"
        ');
        $this->addSql('
            UPDATE claro_field_facet SET type = "date" WHERE type = "3"
        ');
        $this->addSql('
            UPDATE claro_field_facet SET type = "date" WHERE type = "7"
        ');
        $this->addSql('
            UPDATE claro_field_facet SET type = "email" WHERE type = "8"
        ');
        $this->addSql('
            UPDATE claro_field_facet SET type = "html" WHERE type = "9"
        ');
        $this->addSql('
            UPDATE claro_field_facet SET type = "cascade" WHERE type = "10"
        ');
        $this->addSql('
            UPDATE claro_field_facet SET type = "file" WHERE type = "11"
        ');
        $this->addSql('
            UPDATE claro_field_facet SET type = "boolean" WHERE type = "12"
        ');
        $this->addSql('
            UPDATE claro_field_facet SET type = "choice" WHERE type = "13"
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_field_facet CHANGE type type INT NOT NULL
        ');
    }
}
