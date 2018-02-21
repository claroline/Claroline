<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\FlashCardBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\FlashCardBundle\Entity\Deck;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormView;

/**
 * @DI\Service("claroline.flashcard.deck_manager")
 */
class DeckManager
{
    private $om;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "templating" = @DI\Inject("templating")
     * })
     *
     * @param ObjectManager   $om
     * @param EngineInterface $templating
     */
    public function __construct(ObjectManager $om, EngineInterface $templating)
    {
        $this->om = $om;
        $this->templating = $templating;
    }

    /**
     * Creates a flashcard resource.
     *
     * @param Deck $deck
     *
     * @return Deck
     */
    public function create(Deck $deck)
    {
        foreach ($deck->getUserPreferences() as $userPref) {
            $this->om->persist($userPref);
        }
        $this->om->persist($deck);
        $this->om->flush();

        return $deck;
    }

    /**
     * Deletes a flashcard resource.
     *
     * @param Deck $deck
     */
    public function delete(Deck $deck)
    {
        $this->om->remove($deck);
        $this->om->flush();
    }

    /**
     * Returns the content of the result resource form.
     *
     * @param FormView $view
     *
     * @return string
     **/
    public function getDeckFormContent(FormView $view)
    {
        return $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            [
                'form' => $view,
                'resourceType' => 'claroline_flashcard',
            ]
        );
    }
}
