<?php

namespace UJM\ExoBundle\Controller\Tool;

use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use UJM\ExoBundle\Manager\Item\ItemManager;
use UJM\ExoBundle\Serializer\UserSerializer;

/**
 * QuestionBankController
 * The tool permits to users to manage their questions across the platform (edit, export, share, etc.).
 *
 * @EXT\Route("/questions", options={"expose"=true})
 */
class QuestionBankController
{
    /**
     * @var ItemManager
     */
    private $itemManager;

    /**
     * @var UserSerializer
     */
    private $userSerializer;

    /**
     * QuestionBankController constructor.
     *
     * @DI\InjectParams({
     *     "itemManager" = @DI\Inject("ujm_exo.manager.item"),
     *     "userSerializer"  = @DI\Inject("ujm_exo.serializer.user")
     * })
     *
     * @param ItemManager    $itemManager
     * @param UserSerializer $userSerializer
     */
    public function __construct(ItemManager $itemManager, UserSerializer $userSerializer)
    {
        $this->itemManager = $itemManager;
        $this->userSerializer = $userSerializer;
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
            'initialSearch' => $this->itemManager->search($user),
            'currentUser' => $this->userSerializer->serialize($user),
        ];
    }
}
