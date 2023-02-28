<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/03/02 09:36:18
 */
class Version20230302093616 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_panel_facet (
                course_id INT NOT NULL, 
                panel_facet_id INT NOT NULL, 
                INDEX IDX_B108498E591CC992 (course_id), 
                UNIQUE INDEX UNIQ_B108498EF7CB6621 (panel_facet_id), 
                PRIMARY KEY(course_id, panel_facet_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_panel_facet 
            ADD CONSTRAINT FK_B108498E591CC992 FOREIGN KEY (course_id) 
            REFERENCES claro_cursusbundle_course (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_panel_facet 
            ADD CONSTRAINT FK_B108498EF7CB6621 FOREIGN KEY (panel_facet_id) 
            REFERENCES claro_panel_facet (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_panel_facet
        ');
    }
}
