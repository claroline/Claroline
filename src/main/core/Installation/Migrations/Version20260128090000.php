<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260128090000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE claro_resource_rights
            SET creatableTypes = REPLACE(creatableTypes, "file", "file\",\"pdf\",\"video\",\"image\",\"audio") 
            WHERE creatableTypes LIKE "%file%" AND creatableTypes LIKE "[%"
        ');

        $this->addSql('
            UPDATE claro_resource_rights
            SET creatableTypes = REPLACE(creatableTypes, "hevinci_url", "hevinci_url\",\"youtube_video") 
            WHERE creatableTypes LIKE "%hevinci_url%" AND creatableTypes LIKE "[%"
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
