<?php

namespace Claroline\ExternalSynchronizationBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/05/17 11:34:01
 */
class Version20170517113358 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_external_synchronized_user (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                external_user_id VARCHAR(255) NOT NULL, 
                source_slug VARCHAR(255) NOT NULL, 
                last_synchronization_date DATE NOT NULL, 
                UNIQUE INDEX UNIQ_56881D76A76ED395 (user_id), 
                UNIQUE INDEX unique_user_by_source (external_user_id, source_slug), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_external_synchronized_group (
                id INT AUTO_INCREMENT NOT NULL, 
                group_id INT DEFAULT NULL, 
                external_group_id VARCHAR(255) NOT NULL, 
                source_slug VARCHAR(255) NOT NULL, 
                last_synchronization_date DATE NOT NULL, 
                active TINYINT(1) NOT NULL, 
                UNIQUE INDEX UNIQ_DB7D7233FE54D947 (group_id), 
                UNIQUE INDEX unique_group_by_source (external_group_id, source_slug), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_external_synchronized_user 
            ADD CONSTRAINT FK_56881D76A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_external_synchronized_group 
            ADD CONSTRAINT FK_DB7D7233FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_external_synchronized_user
        ');
        $this->addSql('
            DROP TABLE claro_external_synchronized_group
        ');
    }
}
