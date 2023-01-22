<?php

namespace HeVinci\CompetencyBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\InstallationBundle\Updater\Updater;

class Updater130700 extends Updater
{
    /** @var ObjectManager */
    private $om;
    /** @var ToolManager */
    private $toolManager;

    public function __construct(
        ObjectManager $om,
        ToolManager $toolManager
    ) {
        $this->om = $om;
        $this->toolManager = $toolManager;
    }

    public function preUpdate()
    {
        /** @var AdminTool $competencyTool */
        $competencyTool = $this->om->getRepository(AdminTool::class)->findOneBy([
            'name' => 'competencies',
        ]);

        if ($competencyTool) {
            // grant rights to roles which can access the old admin tool
            $orderedTool = $this->toolManager->getOrderedTool('evaluation', Tool::DESKTOP);
            if ($orderedTool) {
                foreach ($competencyTool->getRoles() as $role) {
                    $this->toolManager->setPermissions([
                        'open' => true,
                        'edit' => true,
                    ], $orderedTool, $role);
                }
            }
        }
    }

    public function postUpdate()
    {
        // remove admin competency tool (moved in evaluation tool)
        /** @var AdminTool $competencyTool */
        $competencyTool = $this->om->getRepository(AdminTool::class)->findOneBy([
            'name' => 'competencies',
        ]);

        if ($competencyTool) {
            // let's cascades remove all related records
            $this->om->remove($competencyTool);
            $this->om->flush();
        }
    }
}
