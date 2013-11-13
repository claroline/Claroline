<?php

namespace Icap\LessonBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/11/13 07:07:18
 */
class Version20131113190717 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__lesson_chapter MODIFY (
                title VARCHAR2(255) NOT NULL, 
                slug VARCHAR2(128) NOT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__lesson_chapter MODIFY (
                title VARCHAR2(255) DEFAULT NULL, 
                slug VARCHAR2(128) DEFAULT NULL
            )
        ");
    }
}