<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/11/20 09:52:07
 */
class Version20161120095146 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // Choices
        $this->addSql('
            UPDATE ujm_choice SET right_response=0 WHERE right_response IS NULL
        ');
        $this->addSql('
            UPDATE ujm_choice SET weight=1 WHERE weight IS NULL AND right_response = 1
        ');
        $this->addSql('
            UPDATE ujm_choice SET weight=-1 WHERE weight IS NULL AND right_response = 0
        ');
        $this->addSql('
            ALTER TABLE ujm_choice 
            CHANGE right_response expected TINYINT(1) NOT NULL, 
            CHANGE weight score DOUBLE PRECISION NOT NULL, 
            DROP position_force, 
            CHANGE ordre entity_order INT NOT NULL,
            CHANGE `label` data LONGTEXT DEFAULT NULL
        ');

        // Coords
        $this->addSql('
            ALTER TABLE ujm_coords CHANGE score_coords score DOUBLE PRECISION NOT NULL
        ');

        // Holes
        $this->addSql('
            ALTER TABLE ujm_hole 
            CHANGE selector selector TINYINT(1) NOT NULL
        ');

        // Labels
        $this->addSql('
            ALTER TABLE ujm_label 
            CHANGE score_right_response score DOUBLE PRECISION NOT NULL, 
            DROP position_force, 
            CHANGE ordre entity_order INT NOT NULL,
            CHANGE `value` data LONGTEXT DEFAULT NULL
        ');

        // Proposals
        $this->addSql('
            ALTER TABLE ujm_proposal 
            DROP position_force, 
            CHANGE ordre entity_order INT NOT NULL,
            CHANGE value `data` LONGTEXT DEFAULT NULL
        ');

        // Images
        $this->addSql('
            ALTER TABLE ujm_picture 
            DROP FOREIGN KEY FK_88AACC8AA76ED395
        ');
        $this->addSql('
            DROP INDEX IDX_88AACC8AA76ED395 ON ujm_picture
        ');
        $this->addSql('
            ALTER TABLE ujm_picture 
            DROP user_id,
            CHANGE `label` title VARCHAR(255) NOT NULL
        ');

        // QuestionObjects
        $this->addSql('
            ALTER TABLE ujm_object_question CHANGE ordre entity_order INT NOT NULL
        ');

        // QuestionResources
        $this->addSql('
            ALTER TABLE ujm_question_resource CHANGE `order` entity_order INT NOT NULL
        ');

        // Shared
        $this->addSql('
            ALTER TABLE ujm_share CHANGE allowtomodify adminRights TINYINT(1) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        // Choices
        $this->addSql('
            ALTER TABLE ujm_choice 
            CHANGE score weight DOUBLE PRECISION DEFAULT NULL, 
            ADD position_force TINYINT(1) DEFAULT NULL, 
            CHANGE entity_order ordre INT NOT NULL,
            CHANGE expected right_response TINYINT(1) DEFAULT NULL,
            CHANGE data `label` LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci
        ');

        // Coords
        $this->addSql('
            ALTER TABLE ujm_coords CHANGE score score_coords DOUBLE PRECISION NOT NULL
        ');

        // Holes
        $this->addSql('
            ALTER TABLE ujm_hole 
            CHANGE selector selector TINYINT(1) DEFAULT NULL
        ');

        // Labels
        $this->addSql('
            ALTER TABLE ujm_label 
            CHANGE score score_right_response DOUBLE PRECISION DEFAULT NULL, 
            ADD position_force TINYINT(1) DEFAULT NULL, 
            CHANGE entity_order ordre INT NOT NULL,
            CHANGE data `value` LONGTEXT NOT NULL COLLATE utf8_unicode_ci
        ');

        // Proposals
        $this->addSql('
            ALTER TABLE ujm_proposal 
            ADD position_force TINYINT(1) DEFAULT NULL, 
            CHANGE entity_order ordre INT NOT NULL,
            CHANGE data `value` LONGTEXT NOT NULL COLLATE utf8_unicode_ci
        ');

        // Images
        $this->addSql('
            ALTER TABLE ujm_picture 
            ADD user_id INT DEFAULT NULL,
            CHANGE title `label` VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('
            ALTER TABLE ujm_picture 
            ADD CONSTRAINT FK_88AACC8AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_88AACC8AA76ED395 ON ujm_picture (user_id)
        ');

        // QuestionObjects
        $this->addSql('
            ALTER TABLE ujm_object_question CHANGE entity_order ordre INT NOT NULL
        ');

        // QuestionResources
        $this->addSql('
            ALTER TABLE ujm_question_resource CHANGE entity_order `order` INT NOT NULL
        ');

        // Shared
        $this->addSql('
            ALTER TABLE ujm_share CHANGE adminrights allowToModify TINYINT(1) NOT NULL
        ');
    }
}
