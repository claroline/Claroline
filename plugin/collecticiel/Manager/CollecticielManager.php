<?php

namespace Innova\CollecticielBundle\Manager;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Innova\CollecticielBundle\Entity\Dropzone;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("innova.manager.collecticiel_manager")
 */
class CollecticielManager
{
    /**
     * @DI\InjectParams({
     *     "ch"  = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(PlatformConfigurationHandler $ch)
    {
        $this->ch = $ch;
    }

    /**
     * Import a Collecticiel into the platform.
     *
     * @param array $data
     * @param array $created
     *
     * @return Dropzone
     */
    public function import(array $data, array $created, $archivePath)
    {
        $dropData = $data['data'];

        $dropzone = new Dropzone();

        $dropzone->setInstruction(file_get_contents($archivePath.DIRECTORY_SEPARATOR.$dropData['instruction']));

        $dropzone->setAllowWorkspaceResource($dropData['allow_workspace_resource']);
        $dropzone->setAllowUpload($dropData['allow_upload']);
        $dropzone->setAllowUrl($dropData['allow_url']);
        $dropzone->setAllowRichText($dropData['allow_rich_text']);

        $dropzone->setManualPlanning($dropData['manual_planning']);
        $dropzone->setManualState($dropData['manual_state']);

        if (!empty($dropData['start_allow_drop'])) {
            $dropzone->setStartAllowDrop(\DateTime::createFromFormat('Y-m-d H:i:s', $dropData['start_allow_drop']));
        }

        if (!empty($dropData['end_allow_drop'])) {
            $dropzone->setEndAllowDrop(\DateTime::createFromFormat('Y-m-d H:i:s', $dropData['end_allow_drop']));
        }

        return $dropzone;
    }

    /**
     * Export a Collecticiel.
     *
     * @param Workspace $workspace
     * @param array     $files
     * @param Dropzone  $dropzone
     *
     * @return array
     */
    public function export($workspace, array &$files, Dropzone $dropzone)
    {
        $data = [];

        $uid = uniqid().'.txt';
        $tmpPath = $this->ch->getParameter('tmp_dir').DIRECTORY_SEPARATOR.$uid;
        file_put_contents($tmpPath, $dropzone->getInstruction());
        $files[$uid] = $tmpPath;

        $data['instruction'] = $uid;

        $data['allow_workspace_resource'] = $dropzone->getAllowWorkspaceResource();
        $data['allow_upload'] = $dropzone->getAllowUpload();
        $data['allow_url'] = $dropzone->getAllowUrl();
        $data['allow_rich_text'] = $dropzone->getAllowRichText();

        $data['manual_planning'] = $dropzone->getManualPlanning();
        $data['manual_state'] = $dropzone->getManualState();

        $startDate = $dropzone->getStartAllowDrop();
        if (!empty($startDate)) {
            $data['start_allow_drop'] = $startDate->format('Y-m-d H:i:s');
        }

        $endDate = $dropzone->getEndAllowDrop();
        if (!empty($endDate)) {
            $data['end_allow_drop'] = $endDate->format('Y-m-d H:i:s');
        }

        return $data;
    }
}
