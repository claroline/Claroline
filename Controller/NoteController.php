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
use Claroline\FlashCardBundle\Entity\Note;
use Claroline\FlashCardBundle\Entity\NoteType;
use Claroline\FlashCardBundle\Entity\Deck;
use Claroline\FlashCardBundle\Entity\FieldValue;
use Claroline\FlashCardBundle\Entity\Card;
use Claroline\FlashCardBundle\Entity\CardType;
use Claroline\FlashCardBundle\Manager\NoteManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route(requirements={"id"="\d+", "abilityId"="\d+"}, options={"expose"=true})
 * @EXT\Method("GET")
 */
class NoteController
{
    private $manager;
    private $formHandler;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.flashcard.note_manager"),
     *     "handler" = @DI\Inject("claroline.form_handler"),
     *     "checker" = @DI\Inject("security.authorization_checker"),
     *     "serializer" = @DI\Inject("serializer")
     * })
     *
     * @param NoteManager                   $manager
     * @param FormHandler                   $handler
     * @param AuthorizationCheckerInterface $checker
     */
    public function __construct(
        NoteManager $manager,
        FormHandler $handler,
        AuthorizationCheckerInterface $checker,
        $serializer
    )
    {
        $this->manager = $manager;
        $this->formHandler = $handler;
        $this->checker = $checker;
        $this->serializer = $serializer;
    }

    /**
     * @EXT\Route(
     *     "/note/create/deck/{deck}/note_type/{noteType}", 
     *     name="claroline_create_note"
     * )
     * @EXT\Method("POST")
     *
     * @param Request   $request
     * @param NoteType  $noteType
     * @return JsonResponse
     */
    public function createNoteAction(Request $request, Deck $deck, NoteType $noteType)
    {
        $fields = $request->request->get('fields', false);
        $response = new JsonResponse();

        if($fields !== false) {
            $note = new Note();
            $note->setDeck($deck);
            $note->setNoteType($noteType);

            foreach($fields as $field) {
                $fieldValue = new FieldValue();
                $fieldValue->setFieldLabel($noteType->getFieldLabel($field['id']));
                $fieldValue->setValue($field['value']);
                $fieldValue->setNote($note);
                $note->addFieldValue($fieldValue);
            }

            foreach($noteType->getCardTypes() as $cardType) {
                $card = new Card();
                $card->setCardType($cardType);
                $card->setNote($note);
                $note->addCard($card);
            }

            $note = $this->manager->create($note);

            $context = new SerializationContext();
            $context->setGroups('api_flashcard_deck');
            $response->setData(json_decode(
                $this->serializer->serialize($note, 'json', $context)
            ));
        } else {
            $response->setData('Field "fieldValues" is missing');
            $response->setStatusCode(422);
        }

        return $response;
    }
}
