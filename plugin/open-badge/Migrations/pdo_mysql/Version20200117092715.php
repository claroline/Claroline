<?php

namespace Claroline\OpenBadgeBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/01/17 09:28:16
 */
class Version20200117092715 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_badge_class 
            ADD hideRecipients TINYINT(1) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_badge_class 
            DROP hideRecipients
        ');
    }
}
