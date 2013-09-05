<?php

namespace Claroline\ForumBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/04 05:02:30
 */
class Version20130904170227 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP SEQUENCE claro_resource_id_seq
        ");
        $this->addSql("
            DROP SEQUENCE claro_resource_type_custom_action_id_seq
        ");
        $this->addSql("
            DROP SEQUENCE acl_entries_id_seq
        ");
        $this->addSql("
            DROP SEQUENCE acl_classes_id_seq
        ");
        $this->addSql("
            DROP SEQUENCE acl_security_identities_id_seq
        ");
        $this->addSql("
            DROP SEQUENCE acl_object_identities_id_seq
        ");
        $this->addSql("
            CREATE SEQUENCE claro_scorm_id_seq START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            ALTER TABLE claro_forum MODIFY (
                id NUMBER(10) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message MODIFY (
                id NUMBER(10) DEFAULT NULL, 
                content CLOB DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_forum_options MODIFY (
                id NUMBER(10) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject MODIFY (
                id NUMBER(10) DEFAULT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP SEQUENCE claro_scorm_id_seq
        ");
        $this->addSql("
            CREATE SEQUENCE claro_resource_id_seq START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE SEQUENCE claro_resource_type_custom_action_id_seq START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE SEQUENCE acl_entries_id_seq START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE SEQUENCE acl_classes_id_seq START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE SEQUENCE acl_security_identities_id_seq START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE SEQUENCE acl_object_identities_id_seq START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            ALTER TABLE claro_forum_options MODIFY (
                id NUMBER(10) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message MODIFY (
                id NUMBER(10) DEFAULT NULL, 
                content VARCHAR2(255) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject MODIFY (
                id NUMBER(10) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_forum MODIFY (
                id NUMBER(10) DEFAULT NULL
            )
        ");
    }
}