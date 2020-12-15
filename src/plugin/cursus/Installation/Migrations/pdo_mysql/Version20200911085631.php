<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/09/11 08:56:32
 */
class Version20200911085631 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_session_resources (
                resource_id INT NOT NULL, 
                session_id INT NOT NULL, 
                INDEX IDX_4956113E89329D25 (resource_id), 
                UNIQUE INDEX UNIQ_4956113E613FECDF (session_id), 
                PRIMARY KEY(resource_id, session_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_resources 
            ADD CONSTRAINT FK_4956113E89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_cursusbundle_course_session (id)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_resources 
            ADD CONSTRAINT FK_4956113E613FECDF FOREIGN KEY (session_id) 
            REFERENCES claro_resource_node (id)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            ADD location_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            ADD CONSTRAINT FK_C5F56FDE64D218E FOREIGN KEY (location_id) 
            REFERENCES claro__location (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_C5F56FDE64D218E ON claro_cursusbundle_course_session (location_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_session_resources
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP FOREIGN KEY FK_C5F56FDE64D218E
        ');
        $this->addSql('
            DROP INDEX IDX_C5F56FDE64D218E ON claro_cursusbundle_course_session
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP location_id
        ');
    }
}
