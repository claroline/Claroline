<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/07/06 11:09:12
 */
class Version20210706110833 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD sessionOpening VARCHAR(255) DEFAULT NULL
        ');

        $this->addSql('
            UPDATE claro_cursusbundle_course SET sessionOpening = "first_available"
        ');

        $this->addSql('
            UPDATE claro_cursusbundle_course AS c 
            SET c.sessionOpening = "default"
            WHERE EXISTS (
                SELECT s.id
                FROM claro_cursusbundle_course_session AS s 
                WHERE s.course_id = c.id
                  AND s.default_session = true 
            )
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            DROP sessionOpening
        ');
    }
}
