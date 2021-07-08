<?php

namespace Innova\PathBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/02 06:58:04
 */
class Version20200702065803 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F4856781C06096
        ');
        $this->addSql('
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F4856788BD9C1F
        ');
        $this->addSql('
            DROP INDEX IDX_86F4856781C06096 ON innova_step
        ');
        $this->addSql('
            DROP INDEX IDX_86F4856788BD9C1F ON innova_step
        ');
        $this->addSql('
            ALTER TABLE innova_step 
            DROP activity_id, 
            DROP parameters_id, 
            DROP activity_height
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE innova_step 
            ADD activity_id INT DEFAULT NULL, 
            ADD parameters_id INT DEFAULT NULL, 
            ADD activity_height INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F4856781C06096 FOREIGN KEY (activity_id) 
            REFERENCES claro_activity (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F4856788BD9C1F FOREIGN KEY (parameters_id) 
            REFERENCES claro_activity_parameters (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_86F4856781C06096 ON innova_step (activity_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_86F4856788BD9C1F ON innova_step (parameters_id)
        ');
    }
}
