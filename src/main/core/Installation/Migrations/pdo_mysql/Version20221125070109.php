<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/11/25 07:01:17
 */
class Version20221125070109 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE claro_user SET hasAcceptedTerms = 0 WHERE hasAcceptedTerms IS NULL');

        $this->addSql('
            ALTER TABLE claro_user 
            ADD technical TINYINT(1) NOT NULL DEFAULT "0", 
            CHANGE hasAcceptedTerms hasAcceptedTerms TINYINT(1) NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_user 
            DROP technical, 
            CHANGE hasAcceptedTerms hasAcceptedTerms TINYINT(1) DEFAULT NULL
        ');
    }
}
