<?php

namespace Icap\BlogBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/26 10:16:01
 */
class Version20141126101559 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__blog_widget_list (
                id NUMBER(10) NOT NULL, 
                blog_id NUMBER(10) DEFAULT NULL, 
                widgetInstance_id NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__BLOG_WIDGET_LIST' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__BLOG_WIDGET_LIST ADD CONSTRAINT ICAP__BLOG_WIDGET_LIST_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__BLOG_WIDGET_LIST_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__BLOG_WIDGET_LIST_AI_PK BEFORE INSERT ON ICAP__BLOG_WIDGET_LIST FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__BLOG_WIDGET_LIST_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__BLOG_WIDGET_LIST_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__BLOG_WIDGET_LIST_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__BLOG_WIDGET_LIST_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_6979A1C3DAE07E97 ON icap__blog_widget_list (blog_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6979A1C3AB7B5A55 ON icap__blog_widget_list (widgetInstance_id)
        ");
        $this->addSql("
            ALTER TABLE icap__blog_widget_list 
            ADD CONSTRAINT FK_6979A1C3DAE07E97 FOREIGN KEY (blog_id) 
            REFERENCES icap__blog (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__blog_widget_list 
            ADD CONSTRAINT FK_6979A1C3AB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE icap__blog_widget_list
        ");
    }
}