import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {actions as formActions} from '#/main/app/content/form/store'
import {FormData} from '#/main/app/content/form/containers/data'
import {Button} from '#/main/app/action/components/button'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {selectors} from '#/plugin/flashcard/resources/flashcard/editor/store'
import {selectors as baseSelectors} from '#/plugin/flashcard/resources/flashcard/store/selectors'
import {Card as CardTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'
import {MODAL_CARD} from '#/plugin/flashcard/resources/flashcard/editor/modals/card'
import {Cards} from '#/plugin/flashcard/resources/flashcard/components/cards'

const EditorComponent = props => {

  return <FormData
    level={2}
    title={trans('parameters')}
    name={selectors.FORM_NAME}
    buttons={true}
    target={(flashcardDeck) => ['apiv2_flashcard_deck_update', {id: props.flashcardDeck.id}]}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    definition={[{
      icon: 'fa fa-fw fa-home',
      title: trans('overview'),
      fields: [{
        name: 'overview.display',
        type: 'boolean',
        label: trans('enable_overview'),
        linked: [{
          name: 'overview.message',
          type: 'html',
          label: trans('overview_message'),
          displayed: (flashcardDeck) => get(flashcardDeck, 'overview.display')
        }]
      }]
    }, {
      icon: 'fa fa-fw fa-flag-checkered',
      title: trans('end_page'),
      fields: [{
        name: 'end.display',
        type: 'boolean',
        label: trans('show_end_page'),
        linked: [{
          name: 'end.message',
          type: 'html',
          label: trans('end_message'),
          displayed: (flashcardDeck) => get(flashcardDeck, 'end.display')
        }]
      }]
    }]}
  >
    {0 === props.cards.length &&
      <ContentPlaceholder
        className={'flashcard-empty'}
        size="lg"
        icon="fa fa-image"
        title={trans('no_card', {}, 'flashcard')}
      />}

    {0 !== props.cards.length &&
      <Cards
        cards={props.cards}
        actions={(card) => [{
          name: 'configure',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-cog',
          label: trans('configure', {}, 'actions'),
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
        }]}
      />}

    <Button
      className="btn btn-primary w-100"
      type={MODAL_BUTTON}
      primary={true}
      size="lg"
      label={trans('add_card', {}, 'flashcard')}
      modal={[MODAL_CARD, {
        save: (card) => props.update('cards', [].concat(props.cards, [card]))
      }]}
    />
  </FormData>;
}

EditorComponent.propTypes = {
  path: T.string,
  cards: T.arrayOf(T.shape(
    CardTypes.propTypes
  )),
  update: T.func
}

EditorComponent.defaultProps = {
  cards: []
}

const Editor = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    flashcardDeck: baseSelectors.flashcardDeck(state),
    cards: selectors.cards(state)
  }),
  (dispatch) => ({
    update(prop, value) {
      dispatch(formActions.updateProp(selectors.FORM_NAME, prop, value))
    }
  })
)(EditorComponent)

export {
  Editor
}





