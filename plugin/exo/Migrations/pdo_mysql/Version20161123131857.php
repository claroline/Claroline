<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Migrates questions data.
 *
 * - Adds new mimeTypes to the base Question entity (done here, because the old fields are removed).
 * - Cleans old interaction fields that are no longer used.
 */
class Version20161123131857 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // Open questions
        // Adds words mime-type
        $this->addSql("
            UPDATE ujm_question AS q 
            LEFT JOIN ujm_interaction_open AS o ON (o.question_id = q.id) 
            LEFT JOIN ujm_type_open_question AS t ON (o.typeopenquestion_id = t.id) 
            SET q.mime_type='application/x.words+json'
            WHERE q.type='InteractionOpen' 
              AND t.value != 'long' 
        ");
        // Adds open mime-type
        $this->addSql("
            UPDATE ujm_question AS q 
            LEFT JOIN ujm_interaction_open AS o ON (o.question_id = q.id) 
            LEFT JOIN ujm_type_open_question AS t ON (o.typeopenquestion_id = t.id) 
            SET q.mime_type='application/x.open+json' 
            WHERE q.type='InteractionOpen' 
              AND t.value = 'long'
        ");
        $this->addSql('
            ALTER TABLE ujm_interaction_open 
            DROP FOREIGN KEY FK_BFFE44F46AFD3CF
        ');
        $this->addSql('
            DROP INDEX IDX_BFFE44F46AFD3CF ON ujm_interaction_open
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_open 
            DROP typeopenquestion_id
        ');

        // Match questions
        // Adds match mime-type
        $this->addSql("
            UPDATE ujm_question AS q 
            LEFT JOIN ujm_interaction_matching AS m ON (m.question_id = q.id) 
            LEFT JOIN ujm_type_matching AS t ON (m.type_matching_id = t.id) 
            SET q.mime_type= 'application/x.match+json' 
            WHERE q.type=\"InteractionMatching\" 
              AND t.value = 'To bind' 
        ");
        // Adds set mime-type
        $this->addSql("
            UPDATE ujm_question AS q 
            LEFT JOIN ujm_interaction_matching AS m ON (m.question_id = q.id) 
            LEFT JOIN ujm_type_matching AS t ON (m.type_matching_id = t.id) 
            SET q.mime_type= 'application/x.set+json' 
            WHERE q.type=\"InteractionMatching\" 
              AND t.value = 'To drag' 
        ");
        // Adds pair mime-type
        $this->addSql("
            UPDATE ujm_question AS q 
            LEFT JOIN ujm_interaction_matching AS m ON (m.question_id = q.id) 
            LEFT JOIN ujm_type_matching AS t ON (m.type_matching_id = t.id) 
            SET q.mime_type= 'application/x.pair+json' 
            WHERE q.type=\"InteractionMatching\" 
              AND t.value = 'To pair' 
        ");
        $this->addSql('
            ALTER TABLE ujm_interaction_matching 
            DROP FOREIGN KEY FK_AC9801C7F881A129
        ');
        $this->addSql('
            DROP INDEX IDX_AC9801C7F881A129 ON ujm_interaction_matching
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_matching 
            DROP type_matching_id
        ');

        // Cloze questions
        $this->addSql("
            UPDATE ujm_question SET mime_type = 'application/x.cloze+json' WHERE `type` = 'InteractionHole'
        ");
        $this->addSql('
            ALTER TABLE ujm_interaction_hole 
            DROP `html`
        ');

        // Choice questions
        $this->addSql("
            UPDATE ujm_question SET mime_type = 'application/x.choice+json' WHERE `type` = 'InteractionQCM'
        ");
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm 
            DROP FOREIGN KEY FK_58C3D5A1DCB52A9E
        ');
        $this->addSql('
            DROP INDEX IDX_58C3D5A1DCB52A9E ON ujm_interaction_qcm
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm 
            ADD multiple TINYINT(1) NOT NULL
        ');
        // Migrates TypeQCM data into the new column `multiple`
        $this->addSql('
            UPDATE ujm_interaction_qcm AS q
            LEFT JOIN ujm_type_qcm AS t ON q.type_qcm_id = t.id 
            SET q.multiple = true 
            WHERE t.code = 1
        ');
        $this->addSql('
            UPDATE ujm_interaction_qcm AS q
            LEFT JOIN ujm_type_qcm AS t ON q.type_qcm_id = t.id 
            SET q.multiple = false 
            WHERE t.code = 2
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm 
            DROP type_qcm_id
        ');

        // Graphic questions
        $this->addSql("
            UPDATE ujm_question SET mime_type = 'application/x.graphic+json' WHERE `type` = 'InteractionGraphic'
        ");
    }

    public function down(Schema $schema)
    {
        // Open questions
        $this->addSql('
            ALTER TABLE ujm_interaction_open 
            ADD typeopenquestion_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_open 
            ADD CONSTRAINT FK_BFFE44F46AFD3CF FOREIGN KEY (typeopenquestion_id) 
            REFERENCES ujm_type_open_question (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_BFFE44F46AFD3CF ON ujm_interaction_open (typeopenquestion_id)
        ');

        // Match questions
        $this->addSql('
            ALTER TABLE ujm_interaction_matching 
            ADD type_matching_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_matching 
            ADD CONSTRAINT FK_AC9801C7F881A129 FOREIGN KEY (type_matching_id) 
            REFERENCES ujm_type_matching (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_AC9801C7F881A129 ON ujm_interaction_matching (type_matching_id)
        ');
        $this->addSql('
            ALTER TABLE ujm_object_question CHANGE entity_order `order` INT NOT NULL
        ');

        // Cloze questions
        $this->addSql('
            ALTER TABLE ujm_interaction_hole 
            ADD `html` LONGTEXT NOT NULL COLLATE utf8_unicode_ci
        ');

        // Choice questions
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm 
            ADD type_qcm_id INT DEFAULT NULL, 
            DROP multiple
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm 
            ADD CONSTRAINT FK_58C3D5A1DCB52A9E FOREIGN KEY (type_qcm_id) 
            REFERENCES ujm_type_qcm (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_58C3D5A1DCB52A9E ON ujm_interaction_qcm (type_qcm_id)
        ');
    }
}
