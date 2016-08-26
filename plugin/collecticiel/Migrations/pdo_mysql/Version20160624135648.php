<?php

namespace Innova\CollecticielBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/06/24 01:56:52
 */
class Version20160624135648 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_choice_notation 
            ADD criteria_notation_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_choice_notation 
            ADD CONSTRAINT FK_9ABE6A9224B233E4 FOREIGN KEY (criteria_notation_id) 
            REFERENCES innova_collecticielbundle_grading_notation (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_choice_notation 
            ADD CONSTRAINT FK_9ABE6A929680B7F7 FOREIGN KEY (notation_id) 
            REFERENCES innova_collecticielbundle_notation (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_9ABE6A9224B233E4 ON innova_collecticielbundle_choice_notation (criteria_notation_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_choice_notation 
            DROP FOREIGN KEY FK_9ABE6A9224B233E4
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_choice_notation 
            DROP FOREIGN KEY FK_9ABE6A929680B7F7
        ');
        $this->addSql('
            DROP INDEX IDX_9ABE6A9224B233E4 ON innova_collecticielbundle_choice_notation
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_choice_notation 
            DROP criteria_notation_id
        ');
    }
}
