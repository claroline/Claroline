<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/12/13 10:00:00
 */
class Version20211213100000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // correctly escape " and \n
        $this->addSql('
            UPDATE claro_field_facet_value SET field_value = REPLACE(field_value, "\\n", "\\\\n")
        ');

        $this->addSql('
            UPDATE claro_field_facet_value AS v 
            LEFT JOIN claro_field_facet AS f ON v.fieldFacet_id = f.id 
            SET field_value = CONCAT(\'"\', REPLACE(TRIM(BOTH \'"\' FROM field_value), \'"\', \'\\\\"\'), \'"\') 
            WHERE f.type = "string" OR f.type = "html"
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
