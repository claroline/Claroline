<?php

namespace Innova\AudioRecorderBundle\Controller;

use Innova\AudioRecorderBundle\Entity\AudioRecorderConfiguration;
use Innova\AudioRecorderBundle\Form\Type\AudioRecorderConfigurationType;
use Innova\AudioRecorderBundle\Manager\AudioRecorderManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AudioRecorderController extends Controller
{
    protected $manager;

    /**
     * @DI\InjectParams({
     *      "manager"         = @DI\Inject("innova.audio_recorder.manager"),
     * })
     */
    public function __construct(AudioRecorderManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @EXT\Route(
     *     "/configure/form",
     *     name="audio_recorder_config_form"
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function pluginConfigureFormAction()
    {
        $config = $this->manager->getConfig();
        $form = $this->container->get('form.factory')->create(new AudioRecorderConfigurationType(), $config);

        return $this->render(
            'InnovaAudioRecorderBundle:AudioRecorder:options.form.html.twig',
            ['form' => $form->createView(), 'id' => $config->getId()]
        );
    }

    /**
     * @EXT\Route("/update/configuration/{id}", name="audio_recorder_config_save")
     * @EXT\ParamConverter("config", class="InnovaAudioRecorderBundle:AudioRecorderConfiguration")
     * @EXT\Method("POST")
     */
    public function updateConfigurationAction(AudioRecorderConfiguration $config, Request $request)
    {
        $postData = $request->request->get('audio_recorder_configuration');
        if (isset($postData['max_try']) && isset($postData['max_recording_time'])) {
            $this->manager->updateConfiguration($config, $postData);
            $msg = $this->get('translator')->trans('config_update_success', [], 'tools');
            $this->get('session')->getFlashBag()->set('success', $msg);
        } else {
            $msg = $this->get('translator')->trans('config_update_error', [], 'tools');
            $this->get('session')->getFlashBag()->set('error', $msg);
        }

        return $this->redirectToRoute('audio_recorder_config_form');
    }
}
