<?php

namespace Claroline\OpenBadgeBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/01/10 10:43:16
 */
class Version20200110104239 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_evidence 
            DROP FOREIGN KEY FK_6F68173744E0351
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_evidence 
            ADD CONSTRAINT FK_6F68173744E0351 FOREIGN KEY (rule_id) 
            REFERENCES claro__open_badge_rule (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_evidence 
            DROP FOREIGN KEY FK_6F68173744E0351
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_evidence 
            ADD CONSTRAINT FK_6F68173744E0351 FOREIGN KEY (rule_id) 
            REFERENCES claro__open_badge_rule (id)
        ');
    }
}
