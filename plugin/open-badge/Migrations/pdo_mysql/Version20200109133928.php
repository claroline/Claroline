<?php

namespace Claroline\OpenBadgeBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/01/09 01:39:34
 */
class Version20200109133928 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_rule 
            DROP FOREIGN KEY FK_DE554AC7F7A2C2FC
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_rule 
            ADD CONSTRAINT FK_DE554AC7F7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro__open_badge_badge_class (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_rule 
            DROP FOREIGN KEY FK_DE554AC7F7A2C2FC
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_rule 
            ADD CONSTRAINT FK_DE554AC7F7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro__open_badge_badge_class (id)
        ');
    }
}
