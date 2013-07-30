<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/07/30 09:10:30
 */
class Version20130730091030 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_type_custom_action CHANGE resource_type_id resource_type_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type_custom_action 
            ADD CONSTRAINT FK_4A98967B98EC6B7B FOREIGN KEY (resource_type_id) 
            REFERENCES claro_resource_type (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD CONSTRAINT FK_5E7F4AB889329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD CONSTRAINT FK_5E7F4AB8BF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD CONSTRAINT FK_E4A67CACBF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            ADD CONSTRAINT FK_5D9559DCBF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_tools 
            ADD CONSTRAINT FK_60F90965EC942BCF FOREIGN KEY (plugin_id) 
            REFERENCES claro_plugin (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            ADD CONSTRAINT FK_2D34DB3727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_widget_display (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            ADD CONSTRAINT FK_2D34DB382D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            ADD CONSTRAINT FK_2D34DB3A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            ADD CONSTRAINT FK_2D34DB3FBE885E2 FOREIGN KEY (widget_id) 
            REFERENCES claro_widget (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD CONSTRAINT FK_76CA6C4FEC942BCF FOREIGN KEY (plugin_id) 
            REFERENCES claro_plugin (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_content2region 
            ADD CONSTRAINT FK_8D18942E84A0A3ED FOREIGN KEY (content_id) 
            REFERENCES claro_content (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_content2region 
            ADD CONSTRAINT FK_8D18942E98260155 FOREIGN KEY (region_id) 
            REFERENCES claro_region (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_content2region 
            ADD CONSTRAINT FK_8D18942EAA23F6C8 FOREIGN KEY (next_id) 
            REFERENCES claro_content2region (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_content2region 
            ADD CONSTRAINT FK_8D18942EE9583FF0 FOREIGN KEY (back_id) 
            REFERENCES claro_content2region (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_content2type 
            ADD CONSTRAINT FK_1A2084EF84A0A3ED FOREIGN KEY (content_id) 
            REFERENCES claro_content (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_content2type 
            ADD CONSTRAINT FK_1A2084EFC54C8C93 FOREIGN KEY (type_id) 
            REFERENCES claro_type (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_content2type 
            ADD CONSTRAINT FK_1A2084EFAA23F6C8 FOREIGN KEY (next_id) 
            REFERENCES claro_content2type (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_content2type 
            ADD CONSTRAINT FK_1A2084EFE9583FF0 FOREIGN KEY (back_id) 
            REFERENCES claro_content2type (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_subcontent 
            ADD CONSTRAINT FK_D72E133C2055B9A2 FOREIGN KEY (father_id) 
            REFERENCES claro_content (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_subcontent 
            ADD CONSTRAINT FK_D72E133CDD62C21B FOREIGN KEY (child_id) 
            REFERENCES claro_content (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_subcontent 
            ADD CONSTRAINT FK_D72E133CAA23F6C8 FOREIGN KEY (next_id) 
            REFERENCES claro_subcontent (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_subcontent 
            ADD CONSTRAINT FK_D72E133CE9583FF0 FOREIGN KEY (back_id) 
            REFERENCES claro_subcontent (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP FOREIGN KEY FK_E4A67CACBF396750
        ");
        $this->addSql("
            ALTER TABLE claro_content2region 
            DROP FOREIGN KEY FK_8D18942E84A0A3ED
        ");
        $this->addSql("
            ALTER TABLE claro_content2region 
            DROP FOREIGN KEY FK_8D18942E98260155
        ");
        $this->addSql("
            ALTER TABLE claro_content2region 
            DROP FOREIGN KEY FK_8D18942EAA23F6C8
        ");
        $this->addSql("
            ALTER TABLE claro_content2region 
            DROP FOREIGN KEY FK_8D18942EE9583FF0
        ");
        $this->addSql("
            ALTER TABLE claro_content2type 
            DROP FOREIGN KEY FK_1A2084EF84A0A3ED
        ");
        $this->addSql("
            ALTER TABLE claro_content2type 
            DROP FOREIGN KEY FK_1A2084EFC54C8C93
        ");
        $this->addSql("
            ALTER TABLE claro_content2type 
            DROP FOREIGN KEY FK_1A2084EFAA23F6C8
        ");
        $this->addSql("
            ALTER TABLE claro_content2type 
            DROP FOREIGN KEY FK_1A2084EFE9583FF0
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP FOREIGN KEY FK_5E7F4AB889329D25
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP FOREIGN KEY FK_5E7F4AB8BF396750
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type_custom_action 
            DROP FOREIGN KEY FK_4A98967B98EC6B7B
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type_custom_action CHANGE resource_type_id resource_type_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_subcontent 
            DROP FOREIGN KEY FK_D72E133C2055B9A2
        ");
        $this->addSql("
            ALTER TABLE claro_subcontent 
            DROP FOREIGN KEY FK_D72E133CDD62C21B
        ");
        $this->addSql("
            ALTER TABLE claro_subcontent 
            DROP FOREIGN KEY FK_D72E133CAA23F6C8
        ");
        $this->addSql("
            ALTER TABLE claro_subcontent 
            DROP FOREIGN KEY FK_D72E133CE9583FF0
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            DROP FOREIGN KEY FK_5D9559DCBF396750
        ");
        $this->addSql("
            ALTER TABLE claro_tools 
            DROP FOREIGN KEY FK_60F90965EC942BCF
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            DROP FOREIGN KEY FK_76CA6C4FEC942BCF
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            DROP FOREIGN KEY FK_2D34DB3727ACA70
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            DROP FOREIGN KEY FK_2D34DB382D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            DROP FOREIGN KEY FK_2D34DB3A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            DROP FOREIGN KEY FK_2D34DB3FBE885E2
        ");
    }
}