<?php
namespace Icap\DropzoneBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Entity\User;
use Icap\DropzoneBundle\Entity\Dropzone;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap.manager.correction_manager")
 */

class CorrectionManager
{
    private $container;
    private $maskManager;

        /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container"),
     * 		"maskManager" = @DI\Inject("claroline.manager.mask_manager")
     * })
     */
    public function __construct($container,MaskManager $maskManager)
    {
        $this->container = $container;
        $this->maskManager = $maskManager;
    }


    public function getExaminersByCorrectionMade($corrections)
    {
        
        foreach ($corrections as $correction) {
              var_dump($correction->getUser()->getId());
        }
      
        die;
    }

    

}