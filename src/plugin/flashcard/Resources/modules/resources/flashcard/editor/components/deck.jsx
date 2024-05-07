import React, {useCallback} from 'react'
import {useDispatch, useSelector} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {actions as formActions} from '#/main/app/content/form'
import {EditorPage} from '#/main/app/editor'

import {selectors as editorSelectors} from '#/main/core/resource/editor'
import {Card} from '#/plugin/flashcard/resources/flashcard/components/card'
import {selectors} from '#/plugin/flashcard/resources/flashcard/editor/store'
import {MODAL_CARD} from '#/plugin/flashcard/resources/flashcard/editor/modals/card'

const FlashcardEditorCards = () => {
  const cards = useSelector(selectors.cards)

  const dispatch = useDispatch()
  const update = useCallback((value) => {
    dispatch(formActions.updateProp(editorSelectors.STORE_NAME, 'resource.cards', value))
  }, [editorSelectors.STORE_NAME])

  return (
    <EditorPage
      title={trans('cards', {}, 'flashcard')}
      definition={[
        {
          icon: 'fa fa-fw fa-dice',
          title: trans('attempts_pick', {}, 'flashcard'),
          primary: true,
          fields: [
            {
              name: 'draw',
              type: 'number',
              label: trans('draw_options', {}, 'flashcard'),
              help: trans('options_desc', {}, 'flashcard'),
              options: {
                min: 1,
                max: cards.length
              }
            }
          ]
        }
      ]}
    >
      {isEmpty(cards) &&
        <ContentPlaceholder
          icon="fa fa-image"
          size="lg"
          title={trans('no_card', {}, 'flashcard')}
        />
      }

      {!isEmpty(cards) &&
        <ul className="flashcards">
          {cards.map((card) =>
            <li key={card.id}>
              <Card
                className="flashcard-hoverable"
                card={card}
                mode="edit"
                actions={(card) => [
                  {
                    name: 'edit',
                    type: MODAL_BUTTON,
                    icon: 'fa fa-fw fa-pencil',
                    label: trans('edit', {}, 'actions'),
                    modal: [MODAL_CARD, {
                      card: card,
                      save: (updated) => {
                        const updatedPos = cards.findIndex(current => current.id === updated.id)
                        const newCards = cards.slice(0)
                        newCards[updatedPos] = updated

                        update(newCards)
                      }
                    }]
                  }, {
                    name: 'delete',
                    type: CALLBACK_BUTTON,
                    icon: 'fa fa-fw fa-trash',
                    label: trans('delete', {}, 'actions'),
                    callback: () => {
                      const deletedPos = cards.findIndex(current => current.id === card.id)
                      if (-1 !== deletedPos) {
                        const newCards = cards.slice(0)
                        newCards.splice(deletedPos, 1)

                        update(newCards)
                      }
                    },
                    dangerous: true,
                    confirm: {
                      title: trans('card_delete_confirm', {}, 'flashcard'),
                      message: trans('card_delete_message', {}, 'flashcard'),
                      button: trans('delete', {}, 'actions')
                    }
                  }
                ]}
              />
            </li>
          )}
        </ul>
      }

      <Button
        className="btn btn-primary w-100 my-3"
        type={MODAL_BUTTON}
        primary={true}
        size="lg"
        label={trans('add_card', {}, 'flashcard')}
        modal={[MODAL_CARD, {
          save: (card) => update([].concat(cards, [card]))
        }]}
      />
    </EditorPage>
  )
}

export {
  FlashcardEditorCards
}
