<?php

namespace Innova\PathBundle\Migrations\oci8;

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
            ALTER TABLE innova_pathtemplate MODIFY (description CLOB DEFAULT NULL)
        ");
        $this->addSql("
            ALTER TABLE innova_pathtemplate 
            DROP (\"user\", edit_date)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_pathtemplate 
            ADD (
                \"user\" VARCHAR2(255) NOT NULL, 
                edit_date TIMESTAMP(0) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE innova_pathtemplate MODIFY (description CLOB NOT NULL)
        ");
    }
}