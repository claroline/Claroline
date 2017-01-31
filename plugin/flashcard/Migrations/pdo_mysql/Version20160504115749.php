<?php

namespace Claroline\FlashCardBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/05/04 11:57:53
 */
class Version20160504115749 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_fcbundle_user_preference (
                deck INT NOT NULL,
                user INT NOT NULL,
                new_card_day INT NOT NULL,
                session_duration INT NOT NULL,
                INDEX IDX_9715380E4FAC3637 (deck),
                INDEX IDX_9715380E8D93D649 (user),
                UNIQUE INDEX uniq (user, deck),
                PRIMARY KEY(deck, user)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_fcbundle_note_type (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_fcbundle_note (
                id INT AUTO_INCREMENT NOT NULL,
                deck_id INT DEFAULT NULL,
                noteType_id INT DEFAULT NULL,
                INDEX IDX_4BDB7CC1EB100558 (noteType_id),
                INDEX IDX_4BDB7CC1111948DC (deck_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_fcbundle_card_type (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(255) NOT NULL,
                noteType_id INT DEFAULT NULL,
                INDEX IDX_FA7FA698EB100558 (noteType_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE cardtype_fieldlabel_question (
                cardtype_id INT NOT NULL,
                fieldlabel_id INT NOT NULL,
                INDEX IDX_4FCC37F84E403864 (cardtype_id),
                INDEX IDX_4FCC37F8A9D5EBE4 (fieldlabel_id),
                PRIMARY KEY(cardtype_id, fieldlabel_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE cardtype_fieldlabel_answer (
                cardtype_id INT NOT NULL,
                fieldlabel_id INT NOT NULL,
                INDEX IDX_73F017734E403864 (cardtype_id),
                INDEX IDX_73F01773A9D5EBE4 (fieldlabel_id),
                PRIMARY KEY(cardtype_id, fieldlabel_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_fcbundle_session (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT DEFAULT NULL,
                deck_id INT DEFAULT NULL,
                due_date DATE NOT NULL,
                duration INT NOT NULL,
                INDEX IDX_920ED92BA76ED395 (user_id),
                INDEX IDX_920ED92B111948DC (deck_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE session_card_new (
                session_id INT NOT NULL,
                card_id INT NOT NULL,
                INDEX IDX_B0BFA0EC613FECDF (session_id),
                INDEX IDX_B0BFA0EC4ACC9A20 (card_id),
                PRIMARY KEY(session_id, card_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE session_card_old (
                session_id INT NOT NULL,
                card_id INT NOT NULL,
                INDEX IDX_E401304C613FECDF (session_id),
                INDEX IDX_E401304C4ACC9A20 (card_id),
                PRIMARY KEY(session_id, card_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_fcbundle_field_value (
                id INT AUTO_INCREMENT NOT NULL,
                note_id INT DEFAULT NULL,
                value LONGTEXT NOT NULL,
                fieldLabel_id INT DEFAULT NULL,
                INDEX IDX_738BB66950A389B2 (fieldLabel_id),
                INDEX IDX_738BB66926ED0855 (note_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_fcbundle_card_log (
                id INT AUTO_INCREMENT NOT NULL,
                card_id INT DEFAULT NULL,
                user_id INT DEFAULT NULL,
                date DATE NOT NULL,
                factor DOUBLE PRECISION NOT NULL,
                painfull TINYINT(1) NOT NULL,
                number_repeated INT NOT NULL,
                due_date DATE NOT NULL,
                INDEX IDX_F28F49854ACC9A20 (card_id),
                INDEX IDX_F28F4985A76ED395 (user_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_fcbundle_card_learning (
                id INT AUTO_INCREMENT NOT NULL,
                card_id INT DEFAULT NULL,
                user_id INT DEFAULT NULL,
                factor DOUBLE PRECISION NOT NULL,
                painfull TINYINT(1) NOT NULL,
                number_repeated INT NOT NULL,
                due_date DATE NOT NULL,
                INDEX IDX_D0B7CD7B4ACC9A20 (card_id),
                INDEX IDX_D0B7CD7BA76ED395 (user_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_fcbundle_card (
                id INT AUTO_INCREMENT NOT NULL,
                note_id INT DEFAULT NULL,
                cardType_id INT DEFAULT NULL,
                INDEX IDX_92721E0681FD01F8 (cardType_id),
                INDEX IDX_92721E0626ED0855 (note_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_fcbundle_deck (
                id INT AUTO_INCREMENT NOT NULL,
                new_card_day_default INT NOT NULL,
                session_duration_default INT NOT NULL,
                resourceNode_id INT DEFAULT NULL,
                UNIQUE INDEX UNIQ_CBCAB0E2B87FAB32 (resourceNode_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_fcbundle_field_label (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(255) NOT NULL,
                noteType_id INT DEFAULT NULL,
                INDEX IDX_605BBEB5EB100558 (noteType_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_user_preference
            ADD CONSTRAINT FK_9715380E4FAC3637 FOREIGN KEY (deck)
            REFERENCES claro_fcbundle_deck (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_user_preference
            ADD CONSTRAINT FK_9715380E8D93D649 FOREIGN KEY (user)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_note
            ADD CONSTRAINT FK_4BDB7CC1EB100558 FOREIGN KEY (noteType_id)
            REFERENCES claro_fcbundle_note_type (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_note
            ADD CONSTRAINT FK_4BDB7CC1111948DC FOREIGN KEY (deck_id)
            REFERENCES claro_fcbundle_deck (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_card_type
            ADD CONSTRAINT FK_FA7FA698EB100558 FOREIGN KEY (noteType_id)
            REFERENCES claro_fcbundle_note_type (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE cardtype_fieldlabel_question
            ADD CONSTRAINT FK_4FCC37F84E403864 FOREIGN KEY (cardtype_id)
            REFERENCES claro_fcbundle_card_type (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE cardtype_fieldlabel_question
            ADD CONSTRAINT FK_4FCC37F8A9D5EBE4 FOREIGN KEY (fieldlabel_id)
            REFERENCES claro_fcbundle_field_label (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE cardtype_fieldlabel_answer
            ADD CONSTRAINT FK_73F017734E403864 FOREIGN KEY (cardtype_id)
            REFERENCES claro_fcbundle_card_type (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE cardtype_fieldlabel_answer
            ADD CONSTRAINT FK_73F01773A9D5EBE4 FOREIGN KEY (fieldlabel_id)
            REFERENCES claro_fcbundle_field_label (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_session
            ADD CONSTRAINT FK_920ED92BA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_session
            ADD CONSTRAINT FK_920ED92B111948DC FOREIGN KEY (deck_id)
            REFERENCES claro_fcbundle_deck (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE session_card_new
            ADD CONSTRAINT FK_B0BFA0EC613FECDF FOREIGN KEY (session_id)
            REFERENCES claro_fcbundle_session (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE session_card_new
            ADD CONSTRAINT FK_B0BFA0EC4ACC9A20 FOREIGN KEY (card_id)
            REFERENCES claro_fcbundle_card (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE session_card_old
            ADD CONSTRAINT FK_E401304C613FECDF FOREIGN KEY (session_id)
            REFERENCES claro_fcbundle_session (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE session_card_old
            ADD CONSTRAINT FK_E401304C4ACC9A20 FOREIGN KEY (card_id)
            REFERENCES claro_fcbundle_card (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_field_value
            ADD CONSTRAINT FK_738BB66950A389B2 FOREIGN KEY (fieldLabel_id)
            REFERENCES claro_fcbundle_field_label (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_field_value
            ADD CONSTRAINT FK_738BB66926ED0855 FOREIGN KEY (note_id)
            REFERENCES claro_fcbundle_note (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_card_log
            ADD CONSTRAINT FK_F28F49854ACC9A20 FOREIGN KEY (card_id)
            REFERENCES claro_fcbundle_card (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_card_log
            ADD CONSTRAINT FK_F28F4985A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_card_learning
            ADD CONSTRAINT FK_D0B7CD7B4ACC9A20 FOREIGN KEY (card_id)
            REFERENCES claro_fcbundle_card (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_card_learning
            ADD CONSTRAINT FK_D0B7CD7BA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_card
            ADD CONSTRAINT FK_92721E0681FD01F8 FOREIGN KEY (cardType_id)
            REFERENCES claro_fcbundle_card_type (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_card
            ADD CONSTRAINT FK_92721E0626ED0855 FOREIGN KEY (note_id)
            REFERENCES claro_fcbundle_note (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_deck
            ADD CONSTRAINT FK_CBCAB0E2B87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_field_label
            ADD CONSTRAINT FK_605BBEB5EB100558 FOREIGN KEY (noteType_id)
            REFERENCES claro_fcbundle_note_type (id)
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_fcbundle_note
            DROP FOREIGN KEY FK_4BDB7CC1EB100558
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_card_type
            DROP FOREIGN KEY FK_FA7FA698EB100558
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_field_label
            DROP FOREIGN KEY FK_605BBEB5EB100558
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_field_value
            DROP FOREIGN KEY FK_738BB66926ED0855
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_card
            DROP FOREIGN KEY FK_92721E0626ED0855
        ');
        $this->addSql('
            ALTER TABLE cardtype_fieldlabel_question
            DROP FOREIGN KEY FK_4FCC37F84E403864
        ');
        $this->addSql('
            ALTER TABLE cardtype_fieldlabel_answer
            DROP FOREIGN KEY FK_73F017734E403864
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_card
            DROP FOREIGN KEY FK_92721E0681FD01F8
        ');
        $this->addSql('
            ALTER TABLE session_card_new
            DROP FOREIGN KEY FK_B0BFA0EC613FECDF
        ');
        $this->addSql('
            ALTER TABLE session_card_old
            DROP FOREIGN KEY FK_E401304C613FECDF
        ');
        $this->addSql('
            ALTER TABLE session_card_new
            DROP FOREIGN KEY FK_B0BFA0EC4ACC9A20
        ');
        $this->addSql('
            ALTER TABLE session_card_old
            DROP FOREIGN KEY FK_E401304C4ACC9A20
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_card_log
            DROP FOREIGN KEY FK_F28F49854ACC9A20
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_card_learning
            DROP FOREIGN KEY FK_D0B7CD7B4ACC9A20
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_user_preference
            DROP FOREIGN KEY FK_9715380E4FAC3637
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_note
            DROP FOREIGN KEY FK_4BDB7CC1111948DC
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_session
            DROP FOREIGN KEY FK_920ED92B111948DC
        ');
        $this->addSql('
            ALTER TABLE cardtype_fieldlabel_question
            DROP FOREIGN KEY FK_4FCC37F8A9D5EBE4
        ');
        $this->addSql('
            ALTER TABLE cardtype_fieldlabel_answer
            DROP FOREIGN KEY FK_73F01773A9D5EBE4
        ');
        $this->addSql('
            ALTER TABLE claro_fcbundle_field_value
            DROP FOREIGN KEY FK_738BB66950A389B2
        ');
        $this->addSql('
            DROP TABLE claro_fcbundle_user_preference
        ');
        $this->addSql('
            DROP TABLE claro_fcbundle_note_type
        ');
        $this->addSql('
            DROP TABLE claro_fcbundle_note
        ');
        $this->addSql('
            DROP TABLE claro_fcbundle_card_type
        ');
        $this->addSql('
            DROP TABLE cardtype_fieldlabel_question
        ');
        $this->addSql('
            DROP TABLE cardtype_fieldlabel_answer
        ');
        $this->addSql('
            DROP TABLE claro_fcbundle_session
        ');
        $this->addSql('
            DROP TABLE session_card_new
        ');
        $this->addSql('
            DROP TABLE session_card_old
        ');
        $this->addSql('
            DROP TABLE claro_fcbundle_field_value
        ');
        $this->addSql('
            DROP TABLE claro_fcbundle_card_log
        ');
        $this->addSql('
            DROP TABLE claro_fcbundle_card_learning
        ');
        $this->addSql('
            DROP TABLE claro_fcbundle_card
        ');
        $this->addSql('
            DROP TABLE claro_fcbundle_deck
        ');
        $this->addSql('
            DROP TABLE claro_fcbundle_field_label
        ');
    }
}
