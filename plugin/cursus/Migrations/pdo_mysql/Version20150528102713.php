<?php

namespace Claroline\CursusBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/05/28 10:27:14
 */
class Version20150528102713 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_cursusbundle_courses_widget_config (
                id INT AUTO_INCREMENT NOT NULL, 
                cursus_id INT DEFAULT NULL, 
                widgetInstance_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_1724E274AB7B5A55 (widgetInstance_id), 
                INDEX IDX_1724E27440AEF4B9 (cursus_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_courses_widget_config 
            ADD CONSTRAINT FK_1724E274AB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_courses_widget_config 
            ADD CONSTRAINT FK_1724E27440AEF4B9 FOREIGN KEY (cursus_id) 
            REFERENCES claro_cursusbundle_cursus (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_cursusbundle_courses_widget_config
        ');
    }
}
