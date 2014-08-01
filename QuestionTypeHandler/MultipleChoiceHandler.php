<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SurveyBundle\QuestionTypeHandler;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\SurveyBundle\Entity\AbstractQuestion;
use Claroline\SurveyBundle\Entity\MultipleChoice\Question;
use Claroline\SurveyBundle\Entity\Survey;
use Claroline\SurveyBundle\Form\MultipleChoiceType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;

/**
 * @DI\Service
 * @DI\Tag("claroline.survey.question_type_handler")
 */
class MultipleChoiceHandler extends AbstractQuestionTypeHandler
{
    private $om;
    private $formFactory;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "formFactory"    = @DI\Inject("form.factory"),
     *     "templating"     = @DI\Inject("templating")
     * })
     */
    public function __construct(
        ObjectManager $om,
        FormFactoryInterface $formFactory,
        EngineInterface $templating
    )
    {
        $this->om = $om;
        $this->formFactory = $formFactory;
        $this->templating = $templating;
    }

    public function getSupportedType()
    {
        return 'choice';
    }

    public function getRepository()
    {
        return $this->om->getRepository('ClarolineSurveyBundle:MultipleChoice\Question');
    }

    public function getCreationForm()
    {
        return $this->formFactory
            ->create(new MultipleChoiceType())
            ->createView();
    }

    public function getEditionForm(AbstractQuestion $question)
    {
        return $this->formFactory
            ->create(new MultipleChoiceType(), $question)
            ->createView();
    }

    public function getAnswersView(AbstractQuestion $question)
    {
        // TODO: Implement getResultsView() method.
    }

    public function getAnswerForm(AbstractQuestion $question)
    {
        // TODO: Implement getAnswerForm() method.
    }

    public function registerAnswer(Survey $survey, Request $request, User $user)
    {
        // TODO: Implement registerAnswer() method.
    }

    protected function doCreateQuestion(Survey $survey, Request $request)
    {
        $form = $this->formFactory->create(new MultipleChoiceType(), new Question());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $question = $form->getData();
            $question->setSurvey($survey);
            $this->om->persist($question);
            $this->om->flush();

            return true;
        }

        return $form->createView();
    }

    protected function doEditQuestion(Survey $survey, Request $request)
    {
        $form = $this->formFactory->create(
            new MultipleChoiceType(),
            $this->getQuestion($survey)
        );
        $form->handleRequest($request);

        if ($form->isValid()) {
            $question = $form->getData();
            $question->setSurvey($survey);
            $this->om->flush();

            return true;
        }

        return $form->createView();
    }
}
