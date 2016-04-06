<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\FlashCardBundle\Controller;

use Claroline\CoreBundle\Form\Handler\FormHandler;
use Claroline\FlashCardBundle\Entity\Deck;
use Claroline\FlashCardBundle\Manager\DeckManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @EXT\Route(requirements={"id"="\d+", "abilityId"="\d+"}, options={"expose"=true})
 * @EXT\Method("GET")
 */
class DeckController
{
    private $manager;
    private $formHandler;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.deck.deck_manager"),
     *     "handler" = @DI\Inject("claroline.form_handler"),
     *     "checker" = @DI\Inject("security.authorization_checker")
     * })
     *
     * @param DeckManager              $manager
     * @param FormHandler                   $handler
     * @param AuthorizationCheckerInterface $checker
     */
    public function __construct(
        DeckManager $manager,
        FormHandler $handler,
        AuthorizationCheckerInterface $checker
    )
    {
        $this->manager = $manager;
        $this->formHandler = $handler;
        $this->checker = $checker;
    }

    /**
     * @EXT\Route("/{deck}", name="claroline_open_flashcard")
     * @EXT\Template
     *
     * @param Deck $deck
     * @return array
     */
    public function deckAction(Deck $deck)
    {
        if (!$this->checker->isGranted('OPEN', $deck)) {
            throw new AccessDeniedException();
        }

        return ['_resource' => $deck];
    }
}
