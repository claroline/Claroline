<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/12/13 02:09:29
 */
class Version20181213140927 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE hevinci_ability 
            ADD minEvaluatedResourceCount INT NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE hevinci_ability 
            DROP minEvaluatedResourceCount
        ');
    }
}
