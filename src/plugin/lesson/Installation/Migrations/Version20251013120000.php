<?php

namespace Icap\LessonBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251013120000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // migrates old blog tags
        $this->addSql('
            UPDATE claro_tagbundle_tagged_object AS t
            LEFT JOIN icap__lesson_chapter AS c ON (t.object_name = c.title)
            SET t.object_id = c.uuid, t.object_class = "Icap\\\LessonBundle\\\Entity\\\Chapter"
            WHERE t.object_class = "Icap\\\BlogBundle\\\Entity\\\Post"
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
