import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import { actions as flashcardActions } from '#/plugin/flashcard/resources/flashcard/editor/store'

import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {selectors} from '#/plugin/flashcard/resources/flashcard/editor/store'
import {selectors as baseSelectors} from '#/plugin/flashcard/resources/flashcard/store/selectors'

import {Card} from '#/plugin/flashcard/resources/flashcard/components/card'
import {MODAL_CARD} from '#/plugin/flashcard/resources/flashcard/editor/modals/card'
import {Card as CardTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'

const EditorComponent = props =>
  <FormData
    level={2}
    className="mt-3"
    title={trans('parameters')}
    name={selectors.FORM_NAME}
    buttons={true}
    save={{
      type: CALLBACK_BUTTON,
      callback: () => props.saveForm(props.flashcardDeck.id, props.flashcardDeckData)
    }}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    definition={[
      {
        icon: 'fa fa-fw fa-home',
        title: trans('overview'),
        fields: [
          {
            name: 'overview.display',
            type: 'boolean',
            label: trans('enable_overview'),
            linked: [
              {
                name: 'overview.message',
                type: 'html',
                label: trans('overview_message'),
                displayed: (flashcardDeck) => get(flashcardDeck, 'overview.display')
              }
            ]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'showProgression',
            type: 'boolean',
            label: trans('show_progression', {}, 'flashcard')
          }, {
            name: 'customButtons',
            type: 'boolean',
            label: trans('custom_button_labels', {}, 'flashcard'),
            linked: [
              {
                name: 'rightButtonLabel',
                type: 'string',
                label: trans('right_button_label', {}, 'flashcard'),
                displayed: (flashcardDeck) => get(flashcardDeck, 'customButtons')
              }, {
                name: 'wrongButtonLabel',
                type: 'string',
                label: trans('wrong_button_label', {}, 'flashcard'),
                displayed: (flashcardDeck) => get(flashcardDeck, 'customButtons')
              }
            ]
          }, {
            name: 'showLeitnerRules',
            type: 'boolean',
            label: trans('show_leitner_rules', {}, 'flashcard')
          }
        ]
      },{
        icon: 'fa fa-fw fa-dice',
        title: trans('attempts_pick', {}, 'flashcard'),
        fields: [
          {
            name: 'draw',
            type: 'number',
            label: trans('draw_options', {}, 'flashcard'),
            help: trans('options_desc', {}, 'flashcard'),
            options: {
              min: 1,
              max: props.cards.length
            }
          }
        ]
      }, {
        icon: 'fa fa-fw fa-flag-checkered',
        title: trans('end_page'),
        fields: [
          {
            name: 'end.display',
            type: 'boolean',
            label: trans('show_end_page'),
            linked: [
              {
                name: 'end.message',
                type: 'html',
                label: trans('end_message'),
                displayed: (flashcardDeck) => get(flashcardDeck, 'end.display')
              }
            ]
          }
        ]
      }
    ]}
  >
    {isEmpty(props.cards) &&
      <ContentPlaceholder
        icon="fa fa-image"
        size="lg"
        title={trans('no_card', {}, 'flashcard')}
      />
    }

    {!isEmpty(props.cards) &&
      <ul className="flashcards">
        {props.cards.map((card) =>
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
                      const updatedPos = props.cards.findIndex(current => current.id === updated.id)
                      const newCards = props.cards.slice(0)
                      newCards[updatedPos] = updated
                      props.update('cards', newCards)
                    }
                  }]
                }, {
                  name: 'delete',
                  type: CALLBACK_BUTTON,
                  icon: 'fa fa-fw fa-trash',
                  label: trans('delete', {}, 'actions'),
                  callback: () => {
                    const deletedPos = props.cards.findIndex(current => current.id === card.id)
                    if (-1 !== deletedPos) {
                      const newCards = props.cards.slice(0)
                      newCards.splice(deletedPos, 1)
                      props.update('cards', newCards)
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
        save: (card) => props.update('cards', [].concat(props.cards, [card]))
      }]}
    />
  </FormData>

EditorComponent.propTypes = {
  path: T.string,
  cards: T.arrayOf(T.shape(
    CardTypes.propTypes
  )),
  saveForm: T.func.isRequired,
  flashcardDeckData: T.object,
  flashcardDeck: T.shape({
    id: T.string.isRequired
  }).isRequired,
  update: T.func.isRequired
}

const Editor = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    cards: selectors.cards(state),
    flashcardDeck: baseSelectors.flashcardDeck(state),
    flashcardDeckData: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME))
  }),
  (dispatch) => ({
    update(prop, value) {
      dispatch(formActions.updateProp(selectors.FORM_NAME, prop, value))
    },
    saveForm(id, data) {
      dispatch(flashcardActions.updateFlashcardDeck(id, data))
    }
  })
)(EditorComponent)

export {
  Editor
}
