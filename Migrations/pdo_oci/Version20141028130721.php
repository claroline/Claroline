<?php

namespace Icap\PortfolioBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/28 01:07:23
 */
class Version20141028130721 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__portfolio_teams (
                id NUMBER(10) NOT NULL, 
                team_id NUMBER(10) NOT NULL, 
                portfolio_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__PORTFOLIO_TEAMS' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__PORTFOLIO_TEAMS ADD CONSTRAINT ICAP__PORTFOLIO_TEAMS_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__PORTFOLIO_TEAMS_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__PORTFOLIO_TEAMS_AI_PK BEFORE INSERT ON ICAP__PORTFOLIO_TEAMS FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__PORTFOLIO_TEAMS_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__PORTFOLIO_TEAMS_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__PORTFOLIO_TEAMS_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__PORTFOLIO_TEAMS_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_BBC17F49296CD8AE ON icap__portfolio_teams (team_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_BBC17F49B96B5643 ON icap__portfolio_teams (portfolio_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX portfolio_teams_unique_idx ON icap__portfolio_teams (portfolio_id, team_id)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_comments (
                id NUMBER(10) NOT NULL, 
                portfolio_id NUMBER(10) NOT NULL, 
                sender_id NUMBER(10) NOT NULL, 
                message CLOB NOT NULL, 
                sending_date TIMESTAMP(0) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__PORTFOLIO_COMMENTS' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__PORTFOLIO_COMMENTS ADD CONSTRAINT ICAP__PORTFOLIO_COMMENTS_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__PORTFOLIO_COMMENTS_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__PORTFOLIO_COMMENTS_AI_PK BEFORE INSERT ON ICAP__PORTFOLIO_COMMENTS FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__PORTFOLIO_COMMENTS_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__PORTFOLIO_COMMENTS_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__PORTFOLIO_COMMENTS_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__PORTFOLIO_COMMENTS_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_D4662DE3B96B5643 ON icap__portfolio_comments (portfolio_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D4662DE3F624B39D ON icap__portfolio_comments (sender_id)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_guides (
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) NOT NULL, 
                portfolio_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ICAP__PORTFOLIO_GUIDES' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ICAP__PORTFOLIO_GUIDES ADD CONSTRAINT ICAP__PORTFOLIO_GUIDES_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ICAP__PORTFOLIO_GUIDES_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ICAP__PORTFOLIO_GUIDES_AI_PK BEFORE INSERT ON ICAP__PORTFOLIO_GUIDES FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ICAP__PORTFOLIO_GUIDES_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ICAP__PORTFOLIO_GUIDES_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ICAP__PORTFOLIO_GUIDES_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ICAP__PORTFOLIO_GUIDES_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_27EAB640A76ED395 ON icap__portfolio_guides (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_27EAB640B96B5643 ON icap__portfolio_guides (portfolio_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX portfolio_users_unique_idx ON icap__portfolio_guides (portfolio_id, user_id)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_text (
                id NUMBER(10) NOT NULL, 
                text CLOB DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_teams 
            ADD CONSTRAINT FK_BBC17F49296CD8AE FOREIGN KEY (team_id) 
            REFERENCES claro_team (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_teams 
            ADD CONSTRAINT FK_BBC17F49B96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_comments 
            ADD CONSTRAINT FK_D4662DE3B96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_comments 
            ADD CONSTRAINT FK_D4662DE3F624B39D FOREIGN KEY (sender_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_guides 
            ADD CONSTRAINT FK_27EAB640A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_guides 
            ADD CONSTRAINT FK_27EAB640B96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_text 
            ADD CONSTRAINT FK_89550C61BF396750 FOREIGN KEY (id) 
            REFERENCES icap__portfolio_abstract_widget (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio 
            ADD (
                commentsViewAt TIMESTAMP(0) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD (
                label VARCHAR2(255) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_badges_badge 
            DROP CONSTRAINT FK_25D41B98F7A2C2FC
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_badges_badge 
            ADD CONSTRAINT FK_25D41B98F7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE icap__portfolio_teams
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_comments
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_guides
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_text
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio 
            DROP (commentsViewAt)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP (label)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_badges_badge 
            DROP CONSTRAINT FK_25D41B98F7A2C2FC
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_badges_badge 
            ADD CONSTRAINT FK_25D41B98F7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_user_badge (id)
        ");
    }
}