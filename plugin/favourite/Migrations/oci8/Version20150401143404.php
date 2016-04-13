<?php

namespace HeVinci\FavouriteBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/04/01 02:34:06
 */
class Version20150401143404 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE hevinci_favourite (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                resource_node_id NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ');
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'HEVINCI_FAVOURITE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE HEVINCI_FAVOURITE ADD CONSTRAINT HEVINCI_FAVOURITE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql('
            CREATE SEQUENCE HEVINCI_FAVOURITE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ');
        $this->addSql("
            CREATE TRIGGER HEVINCI_FAVOURITE_AI_PK BEFORE INSERT ON HEVINCI_FAVOURITE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT HEVINCI_FAVOURITE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT HEVINCI_FAVOURITE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'HEVINCI_FAVOURITE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT HEVINCI_FAVOURITE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql('
            CREATE INDEX IDX_55DB0452A76ED395 ON hevinci_favourite (user_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_55DB04521BAD783F ON hevinci_favourite (resource_node_id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_55DB0452A76ED3951BAD783F ON hevinci_favourite (user_id, resource_node_id)
        ');
        $this->addSql('
            ALTER TABLE hevinci_favourite 
            ADD CONSTRAINT FK_55DB0452A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE hevinci_favourite 
            ADD CONSTRAINT FK_55DB04521BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE hevinci_favourite
        ');
    }
}
