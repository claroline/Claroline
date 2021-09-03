<?php

namespace Innova\PathBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/09/03 09:00:00
 */
class Version20210903090000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE innova_step t1
            INNER JOIN innova_step t2
            SET t1.slug = CONCAT(t1.slug, "-", t1.id) 
            WHERE t1.id < t2.id 
              AND t1.slug = t2.slug
              AND t1.path_id = t2.path_id
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
