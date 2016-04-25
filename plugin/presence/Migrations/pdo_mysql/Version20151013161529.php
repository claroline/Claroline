<?php

namespace FormaLibre\PresenceBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/10/13 04:15:31
 */
class Version20151013161529 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('SET foreign_key_checks = 0');
        $this->addSql('
            ALTER TABLE formalibre_presencebundle_presence 
            DROP FOREIGN KEY FK_33952B61FE54D947
        ');
        $this->addSql('
            DROP INDEX IDX_33952B61FE54D947 ON formalibre_presencebundle_presence
        ');
        $this->addSql('
            ALTER TABLE formalibre_presencebundle_presence CHANGE group_id course_session INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE formalibre_presencebundle_presence 
            ADD CONSTRAINT FK_33952B61D887D038 FOREIGN KEY (course_session) 
            REFERENCES claro_cursusbundle_course_session (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_33952B61D887D038 ON formalibre_presencebundle_presence (course_session)
        ');
        $this->addSql('SET foreign_key_checks = 1');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE formalibre_presencebundle_presence 
            DROP FOREIGN KEY FK_33952B61D887D038
        ');
        $this->addSql('
            DROP INDEX IDX_33952B61D887D038 ON formalibre_presencebundle_presence
        ');
        $this->addSql('
            ALTER TABLE formalibre_presencebundle_presence CHANGE course_session group_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE formalibre_presencebundle_presence 
            ADD CONSTRAINT FK_33952B61FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_33952B61FE54D947 ON formalibre_presencebundle_presence (group_id)
        ');
    }
}
