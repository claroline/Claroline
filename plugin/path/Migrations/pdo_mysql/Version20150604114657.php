<?php

namespace Innova\PathBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/06/04 11:47:01
 */
class Version20150604114657 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE innova_path_progression (
                id INT AUTO_INCREMENT NOT NULL, 
                step_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                progression_status VARCHAR(255) NOT NULL, 
                INDEX IDX_960F966A73B21E9C (step_id), 
                INDEX IDX_960F966AA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE innova_path_progression 
            ADD CONSTRAINT FK_960F966A73B21E9C FOREIGN KEY (step_id) 
            REFERENCES innova_step (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE innova_path_progression 
            ADD CONSTRAINT FK_960F966AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE innova_path_progression
        ');
    }
}
