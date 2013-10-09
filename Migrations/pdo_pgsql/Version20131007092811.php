<?php

namespace Innova\PathBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/07 09:28:12
 */
class Version20131007092811 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_pathtemplate 
            DROP \"user\"
        ");
        $this->addSql("
            ALTER TABLE innova_pathtemplate 
            DROP edit_date
        ");
        $this->addSql("
            ALTER TABLE innova_pathtemplate ALTER description 
            DROP NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_pathtemplate 
            ADD \"user\" VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_pathtemplate 
            ADD edit_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_pathtemplate ALTER description 
            SET 
                NOT NULL
        ");
    }
}