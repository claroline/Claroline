<?php

namespace Claroline\FlashcardBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\FlashcardBundle\Entity\FlashcardDeck;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/flashcard_deck")
 */
class FlashcardDeckController extends AbstractCrudController
{
    public function getName(): string
    {
        return 'flashcard_deck';
    }

    public function getClass(): string
    {
        return FlashcardDeck::class;
    }
}
