<?php

namespace Innova\VideoRecorderBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Innova\VideoRecorderBundle\Manager\VideoRecorderManager;
use Innova\VideoRecorderBundle\Entity\VideoRecorderConfiguration;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class VideoRecorderController extends Controller
{
    protected $manager;

    /**
     * @DI\InjectParams({
     *      "manager"         = @DI\Inject("innova.video_recorder.manager")
     * })
     */
    public function __construct(VideoRecorderManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @Route("/update/configuration/{id}", name="video_recorder_config_save")
     * @ParamConverter("config", class="InnovaVideoRecorderBundle:VideoRecorderConfiguration")
     * @Method("POST")
     */
    public function updateConfigurationAction(VideoRecorderConfiguration $config, Request $request)
    {
        if ($request->isMethod('POST')) {
            $postData = $request->request->get('video_recorder_configuration');
            if (isset($postData['max_recording_time'])) {
                $this->manager->updateConfiguration($config, $postData);
                $msg = $this->get('translator')->trans('config_update_success', array(), 'tools');
                $this->get('session')->getFlashBag()->set('success', $msg);
            } else {
                $msg = $this->get('translator')->trans('config_update_error', array(), 'tools');
                $this->get('session')->getFlashBag()->set('error', $msg);
            }

            return $this->redirectToRoute('claro_desktop_open_tool', array('toolName' => 'home'));
        }
    }
}
