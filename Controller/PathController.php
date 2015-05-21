<?php

namespace Innova\PathBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

// Controller dependencies
use Innova\PathBundle\Manager\PathManager;
use Innova\PathBundle\Manager\PublishingManager;
use Innova\PathBundle\Entity\Path\Path;

/**
 * Class PathController
 *
 * @category   Controller
 * @package    Innova
 * @subpackage PathBundle
 * @author     Innovalangues <contact@innovalangues.net>
 * @copyright  2013 Innovalangues
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @version    0.1
 * @link       http://innovalangues.net
 * 
 * @Route(
 *      "/",
 *      name    = "innova_path",
 *      service = "innova_path.controller.path"
 * )
 */
class PathController
{
    /**
     * Current path manager
     * @var \Innova\PathBundle\Manager\PathManager
     */
    protected $pathManager;

    /**
     * Publishing manager
     * @var \Innova\PathBundle\Manager\PublishingManager
     */
    protected $publishingManager;

    /**
     * Class constructor
     *
     * @param \Innova\PathBundle\Manager\PathManager                     $pathManager
     * @param \Innova\PathBundle\Manager\PublishingManager               $publishingManager
     */
    public function __construct(
        PathManager              $pathManager,
        PublishingManager        $publishingManager)
    {
        $this->pathManager       = $pathManager;
        $this->publishingManager = $publishingManager;
    }
    
    /**
     * Publish path
     * Create all needed resources for path to be played
     * 
     * @Route(
     *     "/publish/{id}",
     *     name         = "innova_path_publish",
     *     requirements = {"id" = "\d+"},
     *     options      = {"expose" = true}
     * )
     * @Method("PUT")
     */
    public function publishAction(Path $path)
    {
        $this->pathManager->checkAccess('EDIT', $path);

        $response = array ();
        try {
            $this->publishingManager->publish($path);

            // Publish success
            $response['status']   = 'OK';
            $response['messages'] = array ();
            $response['data']     = json_decode($path->getStructure()); // Send updated data
        } catch (\Exception $e) {
            // Error
            $response['status']   = 'ERROR';
            $response['messages'] = array( $e->getMessage() );
            $response['data']     = null;
        }
        
        return new JsonResponse($response);
    }
}
