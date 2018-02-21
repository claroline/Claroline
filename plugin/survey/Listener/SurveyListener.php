<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SurveyBundle\Listener;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\SurveyBundle\Entity\Choice;
use Claroline\SurveyBundle\Entity\MultipleChoiceQuestion;
use Claroline\SurveyBundle\Entity\Question;
use Claroline\SurveyBundle\Entity\Survey;
use Claroline\SurveyBundle\Entity\SurveyQuestionRelation;
use Claroline\SurveyBundle\Form\SurveyType;
use Claroline\SurveyBundle\Manager\SurveyManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @DI\Service
 */
class SurveyListener
{
    private $formFactory;
    private $httpKernel;
    private $om;
    private $request;
    private $router;
    private $surveyManager;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "formFactory"        = @DI\Inject("form.factory"),
     *     "httpKernel"         = @DI\Inject("http_kernel"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "requestStack"       = @DI\Inject("request_stack"),
     *     "router"             = @DI\Inject("router"),
     *     "surveyManager"      = @DI\Inject("claroline.manager.survey_manager"),
     *     "templating"         = @DI\Inject("templating")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        HttpKernelInterface $httpKernel,
        ObjectManager $om,
        RequestStack $requestStack,
        UrlGeneratorInterface $router,
        SurveyManager $surveyManager,
        TwigEngine $templating
    ) {
        $this->formFactory = $formFactory;
        $this->httpKernel = $httpKernel;
        $this->om = $om;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->surveyManager = $surveyManager;
        $this->templating = $templating;
    }

    /**
     * @DI\Observe("create_form_claroline_survey")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreationForm(CreateFormResourceEvent $event)
    {
        $form = $this->formFactory->create(new SurveyType(), new Survey());
        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            [
                'form' => $form->createView(),
                'resourceType' => 'claroline_survey',
            ]
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_claroline_survey")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $form = $this->formFactory->create(new SurveyType(), new Survey());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $survey = $form->getData();
            $this->om->persist($survey);
            $event->setResources([$survey]);
            $event->stopPropagation();

            return;
        }

        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            [
                'form' => $form->createView(),
                'resourceType' => 'claroline_survey',
            ]
        );
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_claroline_survey")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $params = [];
        $params['_controller'] = 'ClarolineSurveyBundle:Survey:index';
        $params['survey'] = $event->getResource()->getId();
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel
            ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_claroline_survey")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $workspace = $event->getParent()->getWorkspace();
        $survey = $event->getResource();
        $copy = new Survey();
        $copy->setPublished($survey->isPublished());
        $copy->setClosed($survey->isClosed());
        $copy->setHasPublicResult($survey->getHasPublicResult());
        $copy->setAllowAnswerEdition($survey->getAllowAnswerEdition());
        $copy->setStartDate($survey->getStartDate());
        $copy->setEndDate($survey->getEndDate());
        $this->om->persist($copy);
        $relations = $survey->getQuestionRelations();

        foreach ($relations as $relation) {
            $question = $relation->getQuestion();
            $type = $question->getType();
            $copyQuestion = new Question();
            $copyQuestion->setTitle($question->getTitle());
            $copyQuestion->setQuestion($question->getQuestion());
            $copyQuestion->setWorkspace($workspace);
            $copyQuestion->setType($type);
            $copyQuestion->setCommentAllowed($question->isCommentAllowed());
            $copyQuestion->setCommentLabel($question->getCommentLabel());
            $this->om->persist($copyQuestion);

            switch ($type) {
                case 'multiple_choice_single':
                case 'multiple_choice_multiple':
                    $multiChoiceQuestion = $this->surveyManager
                        ->getMultipleChoiceQuestionByQuestion($question);
                    $choices = $multiChoiceQuestion->getChoices();

                    $copyMultiQuestion = new MultipleChoiceQuestion();
                    $copyMultiQuestion->setHorizontal($multiChoiceQuestion->getHorizontal());
                    $copyMultiQuestion->setQuestion($copyQuestion);

                    foreach ($choices as $choice) {
                        $copyChoice = new Choice();
                        $copyChoice->setContent($choice->getContent());
                        $copyChoice->setOther($choice->isOther());
                        $copyChoice->setChoiceQuestion($copyMultiQuestion);
                        $this->om->persist($copyChoice);
                    }

                    $this->om->persist($copyMultiQuestion);
                    break;
                case 'open_ended':
                case 'open_ended_bare':
                case 'simple_text':
                default:
                    break;
            }
            $copyRelation = new SurveyQuestionRelation();
            $copyRelation->setSurvey($copy);
            $copyRelation->setQuestion($copyQuestion);
            $copyRelation->setQuestionOrder($relation->getQuestionOrder());
            $this->om->persist($copyRelation);
        }

        $event->setCopy($copy);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_claroline_survey")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $this->om->remove($event->getResource());
        $event->stopPropagation();
    }
}
