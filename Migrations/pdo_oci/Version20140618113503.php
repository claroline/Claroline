<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/18 11:35:04
 */
class Version20140618113503 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity_parameters 
            ADD (
                who VARCHAR2(255) DEFAULT NULL, 
                \"where\" VARCHAR2(255) DEFAULT NULL, 
                with_tutor NUMBER(1) DEFAULT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity_parameters 
            DROP (who, \"where\", with_tutor)
        ");
    }
}