<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/04/03 07:21:04
 */
class Version20230403072045 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD learner_role_id INT DEFAULT NULL, 
            ADD tutor_role_id INT DEFAULT NULL, 
            DROP tutor_role_name, 
            DROP learner_role_name
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD CONSTRAINT FK_3359D349EF2297F5 FOREIGN KEY (learner_role_id) 
            REFERENCES claro_role (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD CONSTRAINT FK_3359D349BEFB2F13 FOREIGN KEY (tutor_role_id) 
            REFERENCES claro_role (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_3359D349EF2297F5 ON claro_cursusbundle_course (learner_role_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_3359D349BEFB2F13 ON claro_cursusbundle_course (tutor_role_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            DROP FOREIGN KEY FK_3359D349EF2297F5
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            DROP FOREIGN KEY FK_3359D349BEFB2F13
        ');
        $this->addSql('
            DROP INDEX IDX_3359D349EF2297F5 ON claro_cursusbundle_course
        ');
        $this->addSql('
            DROP INDEX IDX_3359D349BEFB2F13 ON claro_cursusbundle_course
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD tutor_role_name VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            ADD learner_role_name VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            DROP learner_role_id, 
            DROP tutor_role_id
        ');
    }
}
