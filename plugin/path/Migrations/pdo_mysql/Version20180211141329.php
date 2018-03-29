<?php

namespace Innova\PathBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/02/11 02:13:31
 */
class Version20180211141329 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_path 
            ADD show_overview TINYINT(1) DEFAULT '1' NOT NULL, 
            ADD show_summary TINYINT(1) DEFAULT '1' NOT NULL,
            ADD uuid VARCHAR(36) NOT NULL
        ");
        $this->addSql('
            UPDATE innova_path SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_CE19F054D17F50A6 ON innova_path (uuid)
        ');
        $this->addSql('
            ALTER TABLE innova_step 
            ADD resource_id INT DEFAULT NULL, 
            ADD title VARCHAR(255) DEFAULT NULL,
            ADD description LONGTEXT DEFAULT NULL,
            ADD uuid VARCHAR(36) NOT NULL,
            CHANGE activity_height activity_height INT DEFAULT NULL
        ');
        $this->addSql('
            UPDATE innova_step SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F4856789329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_86F48567D17F50A6 ON innova_step (uuid)
        ');
        $this->addSql('
            CREATE INDEX IDX_86F4856789329D25 ON innova_step (resource_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_CE19F054D17F50A6 ON innova_path
        ');
        $this->addSql('
            ALTER TABLE innova_path 
            DROP show_overview,  
            DROP show_summary,
            DROP uuid
        ');
        $this->addSql('
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F4856789329D25
        ');
        $this->addSql('
            DROP INDEX UNIQ_86F48567D17F50A6 ON innova_step
        ');
        $this->addSql('
            DROP INDEX IDX_86F4856789329D25 ON innova_step
        ');
        $this->addSql('
            ALTER TABLE innova_step 
            DROP resource_id, 
            DROP title,
            DROP description,
            DROP uuid,
            CHANGE activity_height activity_height INT NOT NULL
        ');
    }
}
