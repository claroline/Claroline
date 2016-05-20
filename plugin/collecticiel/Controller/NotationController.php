<?php
/**
 * Created by : Eric VINCENT
 * Date: 05/16.
 */

namespace Innova\CollecticielBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class NotationController extends DropzoneBaseController
{
    /**
     * @Route(
     *      "/add/notation",
     *      name="innova_collecticiel_add_notation",
     * )
     * @Method("POST")
     * @Template()
     */
    public function AddNotationForDocsInnovaAction()
    {
        die('ici AddNotationForDocsInnovaAction ');
    }
}
