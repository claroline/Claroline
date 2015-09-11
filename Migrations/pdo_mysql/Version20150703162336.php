<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/07/03 04:23:37
 */
class Version20150703162336 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE ujm_exercise_page (
                id INT AUTO_INCREMENT NOT NULL, 
                exercise_player_id INT NOT NULL, 
                position SMALLINT DEFAULT NULL, 
                shuffle TINYINT(1) NOT NULL, 
                is_first_page TINYINT(1) NOT NULL, 
                is_last_page TINYINT(1) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                INDEX IDX_19F33E7D3731F335 (exercise_player_id), 
                UNIQUE INDEX UNIQ_19F33E7DB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE ujm_exercise_page 
            ADD CONSTRAINT FK_19F33E7D3731F335 FOREIGN KEY (exercise_player_id) 
            REFERENCES ujm_exercise_player (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_exercise_page 
            ADD CONSTRAINT FK_19F33E7DB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE ujm_exercise_page
        ");
    }
}