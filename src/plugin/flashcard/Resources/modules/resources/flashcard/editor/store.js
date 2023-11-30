import {createSelector} from 'reselect'
import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'
import {selectors as flashcardSelectors} from '#/plugin/flashcard/resources/flashcard/store/selectors'

import { API_REQUEST } from '#/main/app/api'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {actions as formActions} from '#/main/app/content/form/store'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {trans} from '#/main/app/intl/translation'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'
import {makeReducer} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

const FORM_NAME = `${flashcardSelectors.STORE_NAME}.flashcardForm`

const flashcardDeck = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))

const cards = createSelector(
  [flashcardDeck],
  (flashcardDeck) => flashcardDeck.cards || []
)

const selectors = {
  FORM_NAME,
  flashcardDeck,
  cards
}

export const actions = {}

actions.updateFlashcardDeck = (id, data) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['apiv2_flashcard_deck_update_check', {id: id}],
    request: {
      method: 'PUT',
      body: JSON.stringify(data)
    },
    success: (response) => {
      if(response.resetAttempts) {
        dispatch(modalActions.showModal(MODAL_CONFIRM, {
          icon: 'fa fa-fw fa-exclamation-triangle',
          title: trans('deck_confirm_edit_title', {}, 'flashcard'),
          question: trans('deck_confirm_edit_question', {}, 'flashcard'),
          confirmAction: {
            type: 'callback',
            label: trans('confirm', {}, 'actions'),
            callback: () => {
              dispatch(formActions.saveForm(selectors.FORM_NAME, ['apiv2_flashcard_deck_update', {id: id}]))
            }
          }
        }))
      } else {
        dispatch(formActions.saveForm(selectors.FORM_NAME, ['apiv2_flashcard_deck_update', {id: id}]))
      }
    }
  }
})

const reducer = {
  flashcardForm: makeFormReducer(selectors.FORM_NAME, {}, {
    originalData: makeReducer({}, {
      [makeInstanceAction(RESOURCE_LOAD, 'flashcard')]: (state, action) => action.resourceData.flashcardDeck || state
    }),
    data: makeReducer({}, {
      [makeInstanceAction(RESOURCE_LOAD, 'flashcard')]: (state, action) => action.resourceData.flashcardDeck|| state
    })
  })
}

export {
  reducer,
  selectors
}
