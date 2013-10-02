<?php

namespace Innova\PathBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/01 04:36:51
 */
class Version20131001163650 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step2resourceNode MODIFY (
                resourceOrder NUMBER(10) DEFAULT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step2resourceNode MODIFY (
                resourceOrder NUMBER(10) NOT NULL
            )
        ");
    }
}
