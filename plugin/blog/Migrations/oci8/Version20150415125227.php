<?php

namespace Icap\BlogBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/04/15 12:52:29
 */
class Version20150415125227 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE icap__blog_widget_tag_list_blog (
                id NUMBER(10) NOT NULL, 
                tag_cloud NUMBER(5) NOT NULL, 
                resourceNode_id NUMBER(10) DEFAULT NULL, 
                widgetInstance_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(id)
            )
        ');
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__BLOG_WIDGET_TAG_LIST_BLOG' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__BLOG_WIDGET_TAG_LIST_BLOG ADD CONSTRAINT ICAP__BLOG_WIDGET_TAG_LIST_BLOG_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql('
            CREATE SEQUENCE ICAP__BLOG_WIDGET_TAG_LIST_BLOG_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ');
        $this->addSql("
            CREATE TRIGGER ICAP__BLOG_WIDGET_TAG_LIST_BLOG_AI_PK BEFORE INSERT ON ICAP__BLOG_WIDGET_TAG_LIST_BLOG FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__BLOG_WIDGET_TAG_LIST_BLOG_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__BLOG_WIDGET_TAG_LIST_BLOG_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__BLOG_WIDGET_TAG_LIST_BLOG_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__BLOG_WIDGET_TAG_LIST_BLOG_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql('
            CREATE INDEX IDX_75E7D178B87FAB32 ON icap__blog_widget_tag_list_blog (resourceNode_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_75E7D178AB7B5A55 ON icap__blog_widget_tag_list_blog (widgetInstance_id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX unique_widget_tag_list_blog ON icap__blog_widget_tag_list_blog (
                resourceNode_id, widgetInstance_id
            )
        ');
        $this->addSql('
            ALTER TABLE icap__blog_widget_tag_list_blog 
            ADD CONSTRAINT FK_75E7D178B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ');
        $this->addSql('
            ALTER TABLE icap__blog_widget_tag_list_blog 
            ADD CONSTRAINT FK_75E7D178AB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE UNIQUE INDEX unique_widget_blog ON icap__blog_widget_blog (
                resourceNode_id, widgetInstance_id
            )
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE icap__blog_widget_tag_list_blog
        ');
        $this->addSql('
            DROP INDEX unique_widget_blog
        ');
    }
}
