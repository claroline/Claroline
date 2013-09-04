<?php

namespace Claroline\ForumBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/04 05:02:29
 */
class Version20130904170227 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP SEQUENCE claro_resource_id_seq CASCADE
        ");
        $this->addSql("
            DROP SEQUENCE claro_resource_type_custom_action_id_seq CASCADE
        ");
        $this->addSql("
            DROP SEQUENCE acl_entries_id_seq CASCADE
        ");
        $this->addSql("
            DROP SEQUENCE acl_classes_id_seq CASCADE
        ");
        $this->addSql("
            DROP SEQUENCE acl_security_identities_id_seq CASCADE
        ");
        $this->addSql("
            DROP SEQUENCE acl_object_identities_id_seq CASCADE
        ");
        $this->addSql("
            CREATE SEQUENCE claro_scorm_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        ");
        $this->addSql("
            ALTER TABLE claro_forum ALTER id 
            DROP DEFAULT
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message ALTER id 
            DROP DEFAULT
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message ALTER content TYPE TEXT
        ");
        $this->addSql("
            ALTER TABLE claro_forum_options ALTER id 
            DROP DEFAULT
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject ALTER id 
            DROP DEFAULT
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP SEQUENCE claro_scorm_id_seq CASCADE
        ");
        $this->addSql("
            CREATE SEQUENCE claro_resource_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        ");
        $this->addSql("
            CREATE SEQUENCE claro_resource_type_custom_action_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        ");
        $this->addSql("
            CREATE SEQUENCE acl_entries_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        ");
        $this->addSql("
            CREATE SEQUENCE acl_classes_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        ");
        $this->addSql("
            CREATE SEQUENCE acl_security_identities_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        ");
        $this->addSql("
            CREATE SEQUENCE acl_object_identities_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        ");
        $this->addSql("
            CREATE SEQUENCE claro_forum_options_id_seq
        ");
        $this->addSql("
            SELECT setval(
                'claro_forum_options_id_seq', 
                (
                    SELECT MAX(id) 
                    FROM claro_forum_options
                )
            )
        ");
        $this->addSql("
            ALTER TABLE claro_forum_options ALTER id 
            SET 
                DEFAULT nextval('claro_forum_options_id_seq')
        ");
        $this->addSql("
            CREATE SEQUENCE claro_forum_message_id_seq
        ");
        $this->addSql("
            SELECT setval(
                'claro_forum_message_id_seq', 
                (
                    SELECT MAX(id) 
                    FROM claro_forum_message
                )
            )
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message ALTER id 
            SET 
                DEFAULT nextval('claro_forum_message_id_seq')
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message ALTER content TYPE VARCHAR(255)
        ");
        $this->addSql("
            CREATE SEQUENCE claro_forum_subject_id_seq
        ");
        $this->addSql("
            SELECT setval(
                'claro_forum_subject_id_seq', 
                (
                    SELECT MAX(id) 
                    FROM claro_forum_subject
                )
            )
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject ALTER id 
            SET 
                DEFAULT nextval('claro_forum_subject_id_seq')
        ");
        $this->addSql("
            CREATE SEQUENCE claro_forum_id_seq
        ");
        $this->addSql("
            SELECT setval(
                'claro_forum_id_seq', 
                (
                    SELECT MAX(id) 
                    FROM claro_forum
                )
            )
        ");
        $this->addSql("
            ALTER TABLE claro_forum ALTER id 
            SET 
                DEFAULT nextval('claro_forum_id_seq')
        ");
    }
}