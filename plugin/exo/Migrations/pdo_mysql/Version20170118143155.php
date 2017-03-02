<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/01/18 02:31:57
 */
class Version20170118143155 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE ujm_association (
                id INT AUTO_INCREMENT NOT NULL,
                match_question_id INT DEFAULT NULL,
                label_id INT DEFAULT NULL,
                proposal_id INT DEFAULT NULL,
                score DOUBLE PRECISION NOT NULL,
                feedback LONGTEXT DEFAULT NULL,
                INDEX IDX_2DD0DD0F2CBE8797 (match_question_id),
                INDEX IDX_2DD0DD0F33B92F39 (label_id),
                INDEX IDX_2DD0DD0FF4792058 (proposal_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');

        $this->addSql('
            ALTER TABLE ujm_association
            ADD CONSTRAINT FK_2DD0DD0F2CBE8797 FOREIGN KEY (match_question_id)
            REFERENCES ujm_interaction_matching (id)
        ');

        $this->addSql('
            ALTER TABLE ujm_association
            ADD CONSTRAINT FK_2DD0DD0F33B92F39 FOREIGN KEY (label_id)
            REFERENCES ujm_label (id)
        ');

        $this->addSql('
            ALTER TABLE ujm_association
            ADD CONSTRAINT FK_2DD0DD0FF4792058 FOREIGN KEY (proposal_id)
            REFERENCES ujm_proposal (id)
        ');

        // Create association
        $this->addSql('
          INSERT INTO ujm_association (match_question_id, label_id, proposal_id, score, feedback) (
              SELECT
                  p.interaction_matching_id AS match_question_id,
                  l.id AS label_id,
                  p.id AS proposal_id,
                  IFNULL(l.score, 0),
                  l.feedback
              FROM ujm_proposal AS p
              JOIN ujm_proposal_label AS pa ON (p.id = pa.proposal_id)
              JOIN ujm_label AS l ON (pa.label_id = l.id)
              WHERE pa.proposal_id IS NOT NULL
                AND l.id IS NOT NULL
          )
        ');

        // Create odd for sets
        $this->addSql('
          INSERT INTO ujm_association (match_question_id, label_id, proposal_id, score, feedback) (
              SELECT
                  p.interaction_matching_id AS match_question_id,
                  NULL,
                  p.id AS proposal_id,
                  0,
                  NULL
              FROM ujm_proposal AS p
              JOIN ujm_interaction_matching AS m ON (p.interaction_matching_id = m.id)
              JOIN ujm_question AS q ON (m.question_id = q.id)
              LEFT JOIN ujm_proposal_label AS pa ON (p.id = pa.proposal_id)
              WHERE pa.proposal_id IS NULL
                AND q.mime_type = "application/x.set+json"
          )
        ');

        $this->addSql('
            ALTER TABLE ujm_label
            DROP score,
            DROP feedback
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE ujm_association
        ');
        $this->addSql('
            ALTER TABLE ujm_label
            ADD score DOUBLE PRECISION NOT NULL,
            ADD feedback LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci
        ');
    }
}
