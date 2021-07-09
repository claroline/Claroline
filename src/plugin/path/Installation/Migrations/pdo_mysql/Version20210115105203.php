<?php

namespace Innova\PathBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/01/15 10:52:18
 */
class Version20210115105203 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F48567D96C566B
        ');
        $this->addSql('
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F48567D96C566B FOREIGN KEY (path_id) 
            REFERENCES innova_path (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F48567D96C566B
        ');
        $this->addSql('
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F48567D96C566B FOREIGN KEY (path_id) 
            REFERENCES innova_path (id)
        ');
    }
}
