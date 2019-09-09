<?php

namespace Claroline\OpenBadgeBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/09/09 09:32:26
 */
class Version20190909093222 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_evidence 
            ADD CONSTRAINT FK_6F68173744E0351 FOREIGN KEY (rule_id) 
            REFERENCES claro__open_badge_rule (id)
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_evidence 
            ADD CONSTRAINT FK_6F68173A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
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
            DROP FOREIGN KEY FK_6F68173A76ED395
        ');
    }
}
