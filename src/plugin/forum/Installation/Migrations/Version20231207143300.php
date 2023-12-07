<?php

namespace Claroline\ForumBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231207143300 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE `claro_template_content`
            LEFT JOIN `claro_template` ct on ct.id = `claro_template_content`.`template_id`
            LEFT JOIN `claro_template_type` ctt on ctt.id = ct.`claro_template_type`
            SET `title` = REPLACE(`title`, \'%date%\', \'%post_datetime%\')
            WHERE `title` LIKE \'%date%\' AND ctt.entity_name = \'forum_new_message\'
        ');

        $this->addSql('
            UPDATE `claro_template_content`
            LEFT JOIN `claro_template` ct on ct.id = `claro_template_content`.`template_id`
            LEFT JOIN `claro_template_type` ctt on ctt.id = ct.`claro_template_type`
            SET `content` = REPLACE(`content`, \'%date%\', \'%post_datetime%\')
            WHERE `content` LIKE \'%date%\' AND ctt.entity_name = \'forum_new_message\'
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            UPDATE `claro_template_content`
            LEFT JOIN `claro_template` ct on ct.id = `claro_template_content`.`template_id`
            LEFT JOIN `claro_template_type` ctt on ctt.id = ct.`claro_template_type`
            SET `title` = REPLACE(`title`, \'%post_datetime%\', \'%date%\')
            WHERE `title` LIKE \'%post_datetime%\' AND ctt.entity_name = \'forum_new_message\'
        ');

        $this->addSql('
            UPDATE `claro_template_content`
            LEFT JOIN `claro_template` ct on ct.id = `claro_template_content`.`template_id`
            LEFT JOIN `claro_template_type` ctt on ctt.id = ct.`claro_template_type`
            SET `content` = REPLACE(`content`, \'%post_datetime%\', \'%date%\')
            WHERE `content` LIKE \'%post_datetime%\' AND ctt.entity_name = \'forum_new_message\'
        ');
    }
}
