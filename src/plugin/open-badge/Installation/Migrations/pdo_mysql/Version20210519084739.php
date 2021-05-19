<?php

namespace Claroline\OpenBadgeBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/05/19 08:47:46
 */
class Version20210519084739 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_badge_class 
            ADD template_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_badge_class 
            ADD CONSTRAINT FK_7A1CAEBE5DA0FB8 FOREIGN KEY (template_id) 
            REFERENCES claro_template (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_7A1CAEBE5DA0FB8 ON claro__open_badge_badge_class (template_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_badge_class 
            DROP FOREIGN KEY FK_7A1CAEBE5DA0FB8
        ');
        $this->addSql('
            DROP INDEX IDX_7A1CAEBE5DA0FB8 ON claro__open_badge_badge_class
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_badge_class 
            DROP template_id
        ');
    }
}
