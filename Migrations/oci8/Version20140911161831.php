<?php

namespace Icap\PortfolioBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/11 04:18:33
 */
class Version20140911161831 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_badges (
                id NUMBER(10) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_badges_user_badge (
                id NUMBER(10) NOT NULL, 
                user_badge_id NUMBER(10) NOT NULL, 
                widget_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__PORTFOLIO_WIDGET_BADGES_USER_BADGE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__PORTFOLIO_WIDGET_BADGES_USER_BADGE ADD CONSTRAINT ICAP__PORTFOLIO_WIDGET_BADGES_USER_BADGE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__PORTFOLIO_WIDGET_BADGES_USER_BADGE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__PORTFOLIO_WIDGET_BADGES_USER_BADGE_AI_PK BEFORE INSERT ON ICAP__PORTFOLIO_WIDGET_BADGES_USER_BADGE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__PORTFOLIO_WIDGET_BADGES_USER_BADGE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__PORTFOLIO_WIDGET_BADGES_USER_BADGE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__PORTFOLIO_WIDGET_BADGES_USER_BADGE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__PORTFOLIO_WIDGET_BADGES_USER_BADGE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_E104DE03172F26FC ON icap__portfolio_widget_badges_user_badge (user_badge_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E104DE03FBE885E2 ON icap__portfolio_widget_badges_user_badge (widget_id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_badges 
            ADD CONSTRAINT FK_C1AF804BBF396750 FOREIGN KEY (id) 
            REFERENCES icap__portfolio_abstract_widget (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_badges_user_badge 
            ADD CONSTRAINT FK_E104DE03172F26FC FOREIGN KEY (user_badge_id) 
            REFERENCES claro_user_badge (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_badges_user_badge 
            ADD CONSTRAINT FK_E104DE03FBE885E2 FOREIGN KEY (widget_id) 
            REFERENCES icap__portfolio_widget_badges (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_badges_user_badge 
            DROP CONSTRAINT FK_E104DE03FBE885E2
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_badges
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_badges_user_badge
        ");
    }
}