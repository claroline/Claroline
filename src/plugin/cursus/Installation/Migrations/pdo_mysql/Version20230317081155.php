<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/03/17 08:12:12
 */
class Version20230317081155 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_cursusbundle_session_user_values (
                registration_id INT NOT NULL, 
                value_id INT NOT NULL, 
                INDEX IDX_E930F53D833D8F43 (registration_id), 
                UNIQUE INDEX UNIQ_E930F53DF920BBA2 (value_id), 
                PRIMARY KEY(registration_id, value_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_user_values 
            ADD CONSTRAINT FK_E930F53D833D8F43 FOREIGN KEY (registration_id) 
            REFERENCES claro_cursusbundle_course_session_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_user_values 
            ADD CONSTRAINT FK_E930F53DF920BBA2 FOREIGN KEY (value_id) 
            REFERENCES claro_field_facet_value (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_cursusbundle_session_user_values
        ');
    }
}
