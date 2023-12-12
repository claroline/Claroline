<?php

namespace Claroline\AgendaBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231207141200 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE `claro_template_content`
            LEFT JOIN `claro_template` ct on ct.id = `claro_template_content`.`template_id`
            LEFT JOIN `claro_template_type` ctt on ctt.id = ct.`claro_template_type`
            SET `title` = REPLACE(`title`, \'%session_start%\', \'%session_start_datetime%\')
            WHERE `title` LIKE \'%session_start%\' AND ctt.entity_name LIKE \'%training_%\'
        ');

        $this->addSql('
            UPDATE `claro_template_content`
            LEFT JOIN `claro_template` ct on ct.id = `claro_template_content`.`template_id`
            LEFT JOIN `claro_template_type` ctt on ctt.id = ct.`claro_template_type`
            SET `content` = REPLACE(`content`, \'%session_start%\', \'%session_start_datetime%\')
            WHERE `content` LIKE \'%session_start%\' AND ctt.entity_name LIKE \'%training_%\'
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            UPDATE `claro_template_content`
            LEFT JOIN `claro_template` ct on ct.id = `claro_template_content`.`template_id`
            LEFT JOIN `claro_template_type` ctt on ctt.id = ct.`claro_template_type`
            SET `content` = REPLACE(`content`, \'%session_start_datetime%\', \'%session_start%\')
            WHERE `title` LIKE \'%session_start_datetime%\' AND ctt.entity_name LIKE \'%training_%\'
        ');

        $this->addSql('
            UPDATE `claro_template_content`
            LEFT JOIN `claro_template` ct on ct.id = `claro_template_content`.`template_id`
            LEFT JOIN `claro_template_type` ctt on ctt.id = ct.`claro_template_type`
            SET `content` = REPLACE(`content`, \'%session_start_datetime%\', \'%session_start%\')
            WHERE `content` LIKE \'%session_start_datetime%\' AND ctt.entity_name LIKE \'%training_%\'
        ');
    }
}
