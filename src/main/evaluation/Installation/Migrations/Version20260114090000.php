<?php

namespace Claroline\EvaluationBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2025/09/29 01:57:50
 */
final class Version20260114090000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE claro_tagbundle_tagged_object
            SET object_class = "Claroline\\\EvaluationBundle\\\Entity\\\Sequence\\\Sequence"
            WHERE object_class LIKE "%ResourceNode"
              AND EXISTS (
                  SELECT s.id
                  FROM innova_path AS s
                  WHERE s.uuid = object_id
            )
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
