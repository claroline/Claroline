<?php

namespace Icap\PortfolioBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/13 03:25:46
 */
class Version20150413152543 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget MODIFY (
                col NUMBER(10) DEFAULT 0, 
                \"row\" NUMBER(10) DEFAULT 0
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget MODIFY (
                col NUMBER(10) DEFAULT 1, 
                \"row\" NUMBER(10) DEFAULT 1
            )
        ");
    }
}