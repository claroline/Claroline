<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/11/11 12:49:12
 */
class Version20211111124911 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_session_cancellation (
                id INT AUTO_INCREMENT NOT NULL, 
                session_id INT NOT NULL, 
                user_id INT NOT NULL, 
                registration_date DATETIME NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_CE57DCC1D17F50A6 (uuid), 
                INDEX IDX_CE57DCC1613FECDF (session_id), 
                INDEX IDX_CE57DCC1A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_cancellation 
            ADD CONSTRAINT FK_CE57DCC1613FECDF FOREIGN KEY (session_id) 
            REFERENCES claro_cursusbundle_course_session (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_cancellation 
            ADD CONSTRAINT FK_CE57DCC1A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_session_cancellation
        ');
    }
}
