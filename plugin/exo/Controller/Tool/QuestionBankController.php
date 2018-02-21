<?php

namespace UJM\ExoBundle\Controller\Tool;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use UJM\ExoBundle\Library\Options\Transfer;

/**
 * QuestionBankController
 * The tool permits to users to manage their questions across the platform (edit, export, share, etc.).
 *
 * @EXT\Route("/questions", options={"expose"=true})
 */
class QuestionBankController
{
    /**
     * @var FinderProvider
     */
    private $finder;

    /**
     * @var SerializerProvider
     */
    private $serializer;

    /**
     * QuestionBankController constructor.
     *
     * @DI\InjectParams({
     *     "finder"      = @DI\Inject("claroline.api.finder"),
     *     "serializer"  = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param FinderProvider     $finder
     * @param SerializerProvider $serializer
     */
    public function __construct(
        FinderProvider $finder,
        SerializerProvider $serializer
    ) {
        $this->finder = $finder;
        $this->serializer = $serializer;
    }

    /**
     * Opens the bank of Questions for the current user.
     *
     * @param User $user
     *
     * @EXT\Route("", name="question_bank")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\Template("UJMExoBundle:Tool:question-bank.html.twig")
     *
     * @return array
     */
    public function openAction(User $user)
    {
        return [
            'currentUser' => $this->serializer->serialize($user),
            'questions' => $this->finder->search(
                'UJM\ExoBundle\Entity\Item\Item', [
                    'limit' => 20,
                    'sortBy' => 'content',
                    'filters' => ['selfOnly' => true],
                ], [
                    Transfer::INCLUDE_ADMIN_META,
                ]
            ),
        ];
    }
}
