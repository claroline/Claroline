<?php

namespace Claroline\OpenBadgeBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/09/30 03:03:09
 */
class Version20190930150305 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_assertion 
            DROP FOREIGN KEY FK_B6E0ABAD8D74B52B
        ');
        $this->addSql('
            DROP INDEX IDX_B6E0ABAD8D74B52B ON claro__open_badge_assertion
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_assertion 
            DROP evidences_id
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_assertion 
            ADD evidences_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_assertion 
            ADD CONSTRAINT FK_B6E0ABAD8D74B52B FOREIGN KEY (evidences_id) 
            REFERENCES claro__open_badge_evidence (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_B6E0ABAD8D74B52B ON claro__open_badge_assertion (evidences_id)
        ');
    }
}
