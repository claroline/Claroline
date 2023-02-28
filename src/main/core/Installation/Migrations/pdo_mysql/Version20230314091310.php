<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/03/14 09:13:29
 */
class Version20230314091310 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE claro_field_facet SET position = 0 WHERE position IS NULL 
        ');

        $this->addSql('
            ALTER TABLE claro_field_facet 
            DROP hidden,
            CHANGE position entity_order INT NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_field_facet 
            ADD hidden TINYINT(1) DEFAULT "0" NOT NULL,
            CHANGE entity_order position INT DEFAULT NULL
        ');
    }
}
