<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/11/23 07:33:54
 */
class Version20201123073353 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_home_tab_training_catalog (
                id INT AUTO_INCREMENT NOT NULL, 
                tab_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_2C0073F08D0C9323 (tab_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_training_catalog 
            ADD CONSTRAINT FK_2C0073F08D0C9323 FOREIGN KEY (tab_id) 
            REFERENCES claro_home_tab (id) ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_home_tab_training_catalog
        ');
    }
}
