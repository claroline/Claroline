<?php

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120015 extends Updater
{
    const BATCH_SIZE = 500;

    private $container;
    protected $logger;
    private $om;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function postUpdate()
    {
        $this->removeRightsPermsCreactions();
    }

    private function removeRightsPermsCreactions()
    {
        $unsuported = [
            'activity',
            'shortcut',
            'claroline_survey',
            'claroline_scorm_12',
            'claroline_scorm_2004',
            'innova_collecticiel',
            'icap_dropzone',
            'innova_audio_recorder',
            'innova_video_recorder',
            'innova_media_resource',
            'claroline_flashcard',
            'claroline_chat_room',
            'claroline_big_blue_button',
            'claroline_result',
            'ujm_lti_resource'
        ];

        $typeList = implode(',', array_map(function ($type) {
            return "'{$type}'";
        }, $unsuported));

        $sql = "
          DELETE c FROM claro_list_type_creation c
          JOIN claro_resource_type t on t.id = c.resource_type_id
          WHERE t.name IN ({$typeList})
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
    }
}
