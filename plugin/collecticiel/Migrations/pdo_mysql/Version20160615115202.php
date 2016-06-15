<?php

namespace Innova\CollecticielBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/06/15 11:52:04
 */
class Version20160615115202 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_choice_criteria 
            ADD notation_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_choice_criteria 
            ADD CONSTRAINT FK_2EC94D869680B7F7 FOREIGN KEY (notation_id) 
            REFERENCES innova_collecticielbundle_notation (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_2EC94D869680B7F7 ON innova_collecticielbundle_choice_criteria (notation_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_choice_criteria 
            DROP FOREIGN KEY FK_2EC94D869680B7F7
        ');
        $this->addSql('
            DROP INDEX IDX_2EC94D869680B7F7 ON innova_collecticielbundle_choice_criteria
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_choice_criteria 
            DROP notation_id
        ');
    }
}
