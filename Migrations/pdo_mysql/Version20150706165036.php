<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/07/06 04:50:37
 */
class Version20150706165036 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_exercise_page 
            DROP FOREIGN KEY FK_19F33E7DB87FAB32
        ');
        $this->addSql('
            DROP INDEX UNIQ_19F33E7DB87FAB32 ON ujm_exercise_page
        ');
        $this->addSql('
            ALTER TABLE ujm_exercise_page 
            ADD description LONGTEXT DEFAULT NULL, 
            DROP resourceNode_id
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_exercise_page 
            ADD resourceNode_id INT DEFAULT NULL, 
            DROP description
        ');
        $this->addSql('
            ALTER TABLE ujm_exercise_page 
            ADD CONSTRAINT FK_19F33E7DB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_19F33E7DB87FAB32 ON ujm_exercise_page (resourceNode_id)
        ');
    }
}
