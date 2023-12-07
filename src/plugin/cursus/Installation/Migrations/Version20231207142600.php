<?php

namespace Claroline\CursusBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231207142600 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE `claro_template_content`
            LEFT JOIN `claro_template` ct on ct.id = `claro_template_content`.`template_id`
            LEFT JOIN `claro_template_type` ctt on ctt.id = ct.`claro_template_type`
            SET `title` = REPLACE(`title`, \'%event_start%\', \'%event_start_datetime%\')
            WHERE `title` LIKE \'%event_start%\' AND ctt.entity_name = \'event_invitation\'
        ');

        $this->addSql('
            UPDATE `claro_template_content`
            LEFT JOIN `claro_template` ct on ct.id = `claro_template_content`.`template_id`
            LEFT JOIN `claro_template_type` ctt on ctt.id = ct.`claro_template_type`
            SET `content` = REPLACE(`content`, \'%event_start%\', \'%event_start_datetime%\')
            WHERE `content` LIKE \'%event_start%\' AND ctt.entity_name = \'event_invitation\'
        ');

        $this->addSql('
            UPDATE `claro_template_content`
            LEFT JOIN `claro_template` ct on ct.id = `claro_template_content`.`template_id`
            LEFT JOIN `claro_template_type` ctt on ctt.id = ct.`claro_template_type`
            SET `title` = REPLACE(`title`, \'%event_end%\', \'%event_end_datetime%\')
            WHERE `title` LIKE \'%event_end%\' AND ctt.entity_name = \'event_invitation\'
        ');

        $this->addSql('
            UPDATE `claro_template_content`
            LEFT JOIN `claro_template` ct on ct.id = `claro_template_content`.`template_id`
            LEFT JOIN `claro_template_type` ctt on ctt.id = ct.`claro_template_type`
            SET `content` = REPLACE(`content`, \'%event_end%\', \'%event_end_datetime%\')
            WHERE `content` LIKE \'%event_end%\' AND ctt.entity_name = \'event_invitation\'
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            UPDATE `claro_template_content`
            LEFT JOIN `claro_template` ct on ct.id = `claro_template_content`.`template_id`
            LEFT JOIN `claro_template_type` ctt on ctt.id = ct.`claro_template_type`
            SET `content` = REPLACE(`content`, \'%event_start_datetime%\', \'%event_start%\')
            WHERE `title` LIKE \'%event_start_datetime%\' AND ctt.entity_name = \'event_invitation\'
        ');

        $this->addSql('
            UPDATE `claro_template_content`
            LEFT JOIN `claro_template` ct on ct.id = `claro_template_content`.`template_id`
            LEFT JOIN `claro_template_type` ctt on ctt.id = ct.`claro_template_type`
            SET `content` = REPLACE(`content`, \'%event_start_datetime%\', \'%event_start%\')
            WHERE `content` LIKE \'%event_start_datetime%\' AND ctt.entity_name = \'event_invitation\'
        ');

        $this->addSql('
            UPDATE `claro_template_content`
            LEFT JOIN `claro_template` ct on ct.id = `claro_template_content`.`template_id`
            LEFT JOIN `claro_template_type` ctt on ctt.id = ct.`claro_template_type`
            SET `content` = REPLACE(`content`, \'%event_end_datetime%\', \'%event_end%\')
            WHERE `title` LIKE \'%event_end_datetime%\' AND ctt.entity_name = \'event_invitation\'
        ');

        $this->addSql('
            UPDATE `claro_template_content`
            LEFT JOIN `claro_template` ct on ct.id = `claro_template_content`.`template_id`
            LEFT JOIN `claro_template_type` ctt on ctt.id = ct.`claro_template_type`
            SET `content` = REPLACE(`content`, \'%event_end_datetime%\', \'%event_end%\')
            WHERE `content` LIKE \'%event_end_datetime%\' AND ctt.entity_name = \'event_invitation\'
        ');
    }
}
