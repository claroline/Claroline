<?php

namespace Claroline\CursusBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/08/14 08:58:34
 */
class Version20180814085831 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_cursusbundle_course_session SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_C5F56FDED17F50A6 ON claro_cursusbundle_course_session (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_user 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_cursusbundle_course_session_user SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_80B4120FD17F50A6 ON claro_cursusbundle_course_session_user (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_group 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_cursusbundle_course_session_group SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_F27287A4D17F50A6 ON claro_cursusbundle_course_session_group (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_cursusbundle_session_event SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_257C3061D17F50A6 ON claro_cursusbundle_session_event (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_set 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_cursusbundle_session_event_set SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_C400AB6DD17F50A6 ON claro_cursusbundle_session_event_set (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus_user 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_cursusbundle_cursus_user SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_8AA52D8D17F50A6 ON claro_cursusbundle_cursus_user (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_registration_queue 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_cursusbundle_course_registration_queue SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_E068776ED17F50A6 ON claro_cursusbundle_course_registration_queue (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus_group 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_cursusbundle_cursus_group SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_EA4DDE93D17F50A6 ON claro_cursusbundle_cursus_group (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_cursusbundle_session_event_user SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_31D741DDD17F50A6 ON claro_cursusbundle_session_event_user (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_comment 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_cursusbundle_session_event_comment SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_21DFDBA8D17F50A6 ON claro_cursusbundle_session_event_comment (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_registration_queue 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_cursusbundle_course_session_registration_queue SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_334FC296D17F50A6 ON claro_cursusbundle_course_session_registration_queue (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_document_model 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_cursusbundle_document_model SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_A346BB4DD17F50A6 ON claro_cursusbundle_document_model (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_E068776ED17F50A6 ON claro_cursusbundle_course_registration_queue
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_registration_queue 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_C5F56FDED17F50A6 ON claro_cursusbundle_course_session
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_F27287A4D17F50A6 ON claro_cursusbundle_course_session_group
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_group 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_80B4120FD17F50A6 ON claro_cursusbundle_course_session_user
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_user 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_EA4DDE93D17F50A6 ON claro_cursusbundle_cursus_group
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus_group 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_8AA52D8D17F50A6 ON claro_cursusbundle_cursus_user
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus_user 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_257C3061D17F50A6 ON claro_cursusbundle_session_event
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_21DFDBA8D17F50A6 ON claro_cursusbundle_session_event_comment
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_comment 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_C400AB6DD17F50A6 ON claro_cursusbundle_session_event_set
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_set 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_31D741DDD17F50A6 ON claro_cursusbundle_session_event_user
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_334FC296D17F50A6 ON claro_cursusbundle_course_session_registration_queue
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_registration_queue 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_A346BB4DD17F50A6 ON claro_cursusbundle_document_model
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_document_model 
            DROP uuid
        ');
    }
}
